<?php

namespace App\Jobs;

use App\Models\AudioPipeline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Telepath\Laravel\Facades\Telepath;
use Telepath\Telegram\InlineKeyboardButton;
use Telepath\Telegram\InlineKeyboardMarkup;

class SendTranscribedText implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected AudioPipeline $pipeline,
        protected int $chatId,
        protected string $text,
    ) {}

    public function handle(): void
    {
        $parts = $this->splitMessages($this->text);

        $summarizeButton = InlineKeyboardButton::make(
            text: 'Zusammenfassung',
            callback_data: 'summarize'
        );

        foreach ($parts as $text) {
            Telepath::bot()->sendMessage(
                chat_id: $this->chatId,
                text: $text,
                reply_markup: InlineKeyboardMarkup::make(
                    [
                        [$summarizeButton],
                    ]
                )
            );
        }

        Cleanup::dispatch($this->pipeline);
    }

    /**
     * @param  string  $text
     * @return string[]
     */
    protected function splitMessages(string $text): array
    {
        $parts = [];
        while (strlen($text) > 0) {
            $truncated = Str::limit($text, 4096, '');
            $parts[] = $truncated;
            $text = substr($text, strlen($truncated));
        }
        return $parts;
    }

}
