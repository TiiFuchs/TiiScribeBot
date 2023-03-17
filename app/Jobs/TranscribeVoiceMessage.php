<?php

namespace App\Jobs;

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

    public function __construct(
        protected int $chatId,
        protected string $filename,
    ) {}

    public function handle(Client $openai)
    {
        // OpenAI
        $response = $openai->audio()->transcribe([
            'file'     => fopen(storage_path("app/voice/$this->filename"), 'rb'),
            'model'    => 'whisper-1',
            'language' => 'de',
        ]);

        Telepath::bot()->sendMessage(
            $this->chatId,
            $response->text,
        );

    }

}
