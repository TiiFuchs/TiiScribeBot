<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;

class ConvertFiletype implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $chatId,
        protected string $filename,
    ) {}

    public function handle(): void
    {
        $input = storage_path("app/voice/$this->filename");
        $filename = basename($this->filename, '.oga') . '.mp3';
        $output = storage_path("app/voice/$filename");

        $result = Process::run("ffmpeg -i \"{$input}\" -vn -acodec libmp3lame -q:a 4 \"{$output}\"");

        if (! $result->successful()) {
            throw new \Exception($result->errorOutput());
        }

        TranscribeVoiceMessage::dispatch(
            $this->chatId,
            $filename,
        );
    }

}
