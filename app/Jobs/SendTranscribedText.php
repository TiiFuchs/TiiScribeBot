<?php

namespace App\Jobs;

use App\Models\AudioPipeline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
                reply_markup: count($parts) === 1
                    ? InlineKeyboardMarkup::make(
                        [
                            [$summarizeButton],
                        ]
                    )
                    : null
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
        $words = preg_split('/\s+/', $text);// split text into words
        $chunks = array_reduce($words, function ($carry, $word) {
            $lastIndex = count($carry) - 1;
            if ($lastIndex < 0 || strlen($carry[$lastIndex]) + strlen($word) + 1 > 4096) {
                // create a new chunk if the last chunk is too long or there are no chunks
                $carry[] = $word;
            } else {
                // add the word to the last chunk
                $carry[$lastIndex] .= ' ' . $word;
            }
            return $carry;
        }, []);
        return array_map('trim', $chunks); // remove leading/trailing spaces from chunks
    }

}
