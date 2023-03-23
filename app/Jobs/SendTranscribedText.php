<?php

namespace App\Jobs;

use App\Models\AudioPipeline;
use App\Models\Transcript;
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
        $transcript = Transcript::create([
            'user_id' => $this->chatId,
            'text'    => $this->text,
        ]);

        $parts = $this->splitMessages($this->text);

        $summarizeButton = InlineKeyboardButton::make(
            text: 'tl;dr',
            callback_data: "summarize:{$transcript->id}",
        );

        foreach ($parts as $index => $text) {

            $replyMarkup = ($index === count($parts) - 1)
                ? InlineKeyboardMarkup::make([[$summarizeButton]])
                : null;

            $message = Telepath::bot()->sendMessage(
                chat_id: $this->chatId,
                text: $text,
                reply_markup: $replyMarkup
            );

        }

        $transcript->update([
            'message_id' => $message->message_id,
        ]);

        ClearTranscript::dispatch($transcript)
            ->delay(now()->addMinutes(30));

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
