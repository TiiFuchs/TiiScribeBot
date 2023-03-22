<?php

namespace App\Jobs;

use App\Models\AudioFile;
use App\Models\AudioPipeline;
use App\Services\Dolby\Authentication\GenerateApiToken;
use App\Services\Dolby\DolbyAuthConnector;
use App\Services\Dolby\DolbyConnector;
use App\Services\Dolby\Enhance\GetEnhanceResults;
use App\Services\Dolby\Enhance\StartEnhancing;
use App\Services\Dolby\Input\GetUploadUrl;
use App\Services\Dolby\Output\GetDownloadUrl;
use App\Support\HasStatusMessage;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Telepath\Laravel\Facades\Telepath;
use Telepath\Types\InputFile;

class EnhanceAudioJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    use HasStatusMessage;

    public function __construct(
        protected AudioPipeline $pipeline,
        protected int $chatId,
    ) {}

    protected DolbyConnector $dolbyApi;

    public function handle(): void
    {
        $this->pipeline->setStatusMessage(
            Telepath::bot()->sendMessage(
                chat_id: $this->chatId,
                text: '🔧 Sprachnachricht wird verbessert...'
            )
        );

        $this->dolbyApi = $this->authenticatedConnector();

        // Upload file
        $uploadUrl = $this->getUploadUrl($this->pipeline->input);
        $this->uploadFile($uploadUrl, $this->pipeline->input);

        // Enhance Audio
        $jobId = $this->enhanceAudio(
            $this->pipeline->input,
            $this->pipeline->makeOutput('enhanced')
        );
        $success = $this->waitForEnhancedAudio($jobId);

        if (! $success) {
            // Edit message to "Verbessern der Sprachnachricht fehlgeschlagen"
            $this->setStatus('🔧 ⚡ Verbessern der Sprachnachricht fehlgeschlagen.');
            return;
        }

        $this->setStatus('🔧 ✅ Sprachnachricht erfolgreich verbessert.');

        // Download file
        $downloadUrl = $this->getDownloadUrl($this->pipeline->output);
        $this->downloadFile($downloadUrl, $this->pipeline->output);

        // Send back enhanced voice message
        $this->sendEnhancedVoiceMessage();

        ConvertFiletype::dispatch(
            $this->pipeline->nextStep(),
            $this->chatId,
        );

    }

    protected function authenticatedConnector(): DolbyConnector
    {
        $response = (new DolbyAuthConnector)->send(new GenerateApiToken(
            config('services.dolby.app_key'),
            config('services.dolby.app_secret'),
        ));
        $dolbyAccessToken = $response->json('access_token');

        return new DolbyConnector($dolbyAccessToken);
    }

    protected function getUploadUrl(AudioFile $file): mixed
    {
        $response = $this->dolbyApi->send(new GetUploadUrl(
            'dlb://' . $file->path,
        ));
        return $response->json('url');
    }

    protected function uploadFile(string $uploadUrl, AudioFile $file): bool
    {
        $client = new Client();
        $response = $client->put($uploadUrl, [
            'body' => $file->read(),
        ]);

        return $response->getStatusCode() === 200;
    }

    protected function enhanceAudio(AudioFile $file, AudioFile $target): string
    {
        $response = $this->dolbyApi->send(new StartEnhancing(
            'dlb://' . $file->path,
            'dlb://' . $target->path,
            'mobile_phone',
            [
                'noise' => [
                    'reduction' => [
                        'enable' => true,
                    ],
                ],
            ]
        ));

        return $response->json('job_id');
    }

    protected function waitForEnhancedAudio(string $jobId, int $intervals = 5): bool
    {
        $finished = false;

        do {
            $response = $this->dolbyApi->send(new GetEnhanceResults(
                $jobId,
            ));

            $status = $response->json('status');
            // can be: Pending Running Success Failed Cancelled InternalError

            if ($status === 'Pending' || $status === 'Running') {
                $progress = $response->json('progress');

                $this->setStatus("🔧 Sprachnachricht wird verbessert... ($progress %)");

                sleep($intervals);
            } else {
                $finished = true;
            }
        } while (! $finished);

        return $status === 'Success';
    }

    protected function getDownloadUrl(AudioFile $file)
    {
        $response = $this->dolbyApi->send(new GetDownloadUrl(
            'dlb://' . $file->path
        ));

        return $response->json('url');
    }

    protected function downloadFile(string $downloadUrl, AudioFile $file)
    {
        $client = new Client(
            $this->dolbyApi->config()->all()
        );
        $client->get($downloadUrl, [
            'sink' => $file->write(),
        ]);
    }

    protected function sendEnhancedVoiceMessage()
    {
        $chatId = $this->pipeline->statusMessage()['chat_id'];

        if (pathinfo($this->pipeline->output->path, PATHINFO_EXTENSION) === 'oga') {

            Telepath::bot()->sendVoice(
                chat_id: $chatId,
                voice: InputFile::fromResource($this->pipeline->output->read())
            );

            return;

        }

        // Try to convert it to oga
        $ogaFile = new AudioFile(
            'voice/' . $this->pipeline->output->derivedName('voiced', 'oga')
        );

        $this->pipeline->pushFile($ogaFile);

        $result = \Process::run("ffmpeg -i \"{$this->pipeline->output->fullPath()}\" -c:a libopus \"{$ogaFile->fullPath()}\"");

        if ($result->successful()) {

            Telepath::bot()->sendVoice(
                chat_id: $chatId,
                voice: InputFile::fromResource($ogaFile->read())
            );

        } else {

            Telepath::bot()->sendAudio(
                chat_id: $chatId,
                audio: InputFile::fromResource($this->pipeline->output->read())
            );

        }

    }

}
