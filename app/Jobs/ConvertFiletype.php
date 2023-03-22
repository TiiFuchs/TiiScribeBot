<?php

namespace App\Jobs;

use App\Models\AudioPipeline;
use App\Support\HasStatusMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;

class ConvertFiletype implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    use HasStatusMessage;

    public function __construct(
        protected AudioPipeline $pipeline,
        protected int $chatId,
    ) {}

    public function handle(): void
    {
        $this->pipeline->setStatusMessage(
            \Telepath::bot()->sendMessage(
                chat_id: $this->chatId,
                text: '🔄 Sprachnachricht wird konvertiert...'
            )
        );

        $this->pipeline->makeOutput('converted', 'mp3');

        $input = $this->pipeline->input->fullPath();
        $output = $this->pipeline->output->fullPath();

        $result = Process::run("ffmpeg -i \"{$input}\" -vn -acodec libmp3lame -q:a 4 \"{$output}\"");

        if (! $result->successful()) {
            $this->setStatus('🔄 ⚡ Konvertierung der Sprachnachricht fehlgeschlagen.');
            throw new \Exception($result->errorOutput());
        }

        $this->setStatus('🔄 ✅ Konvertierung abgeschlossen.');

        TranscribeVoiceMessage::dispatch(
            $this->pipeline->nextStep(),
            $this->chatId,
        );
    }

}
