<?php

namespace App\Jobs;

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

        // OpenAI
        $response = $openai->audio()->transcribe([
            'file'     => $this->pipeline->input->read(),
            'model'    => 'whisper-1',
//            'language' => 'de',
        ]);

        $this->setStatus('🖊️ ✅ Transkription abgeschlossen. Folgender Text wurde erkannt:');

        Telepath::bot()->sendMessage(
            chat_id: $this->chatId,
            text: $response->text,
            parse_mode: 'HTML',
        );

        $this->pipeline->cleanupFiles();

    }

}
