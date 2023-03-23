<?php

namespace App\Jobs;

use App\Models\AudioFile;
use App\Models\AudioPipeline;
use App\Support\HasStatusMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OpenAI\Client;
use Telepath\Laravel\Facades\Telepath;

class TranscribeVoiceMessage implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    use HasStatusMessage;

    public function __construct(
        protected AudioPipeline $pipeline,
        protected int $chatId,
    ) {}

    public function handle(Client $openai)
    {
        $this->pipeline->setStatusMessage(
            Telepath::bot()->sendMessage(
                chat_id: $this->chatId,
                text: '🖊️ Sprachnachricht wird transkribiert...'
            )
        );

        $mp3File = $this->convertToMp3($this->pipeline->input);

        if (! $mp3File) {
            $this->setStatus('🖊️ ⚡ Transkription fehlgeschlagen.');
            return;
        }

        // OpenAI
        $response = $openai->audio()->transcribe([
            'file'  => $mp3File->read(),
            'model' => 'whisper-1',
        ]);

        $this->setStatus('🖊️ ✅ Transkription abgeschlossen.');

        SendTranscribedText::dispatch(
            $this->pipeline,
            $this->chatId,
            $response->text,
        );

    }

    protected function convertToMp3(AudioFile $input): ?AudioFile
    {
        if (pathinfo($input->path, PATHINFO_EXTENSION) === 'mp3') {
            return $input;
        }

        $output = $input->derive('converted', 'mp3');
        $this->pipeline->pushFile($output);

        $result = \Process::run("ffmpeg -i \"{$input->fullPath()}\" -vn -acodec libmp3lame -q:a 4 \"{$output->fullPath()}\"");

        if (! $result->successful()) {
            return null;
        }

        return $output;

    }

}
