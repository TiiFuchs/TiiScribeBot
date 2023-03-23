<?php

namespace App\Telepath;

use App\Models\Transcript;
use App\Telepath\Middleware\Can;
use Illuminate\Support\Facades\Cache;
use OpenAI\Client;
use Telepath\Bot;
use Telepath\Handlers\CallbackQuery\CallbackQueryData;
use Telepath\Middleware\Attributes\Middleware;
use Telepath\Telegram\InlineKeyboardMarkup;
use Telepath\Telegram\Update;

#[Middleware(Can::class, 'access')]
class Summarize
{

    public function __construct(
        protected Bot $bot,
    ) {}

    #[CallbackQueryData(prefix: 'summarize:')]
    public function summarize(Update $update)
    {
        $transcriptId = explode(':', $update->callback_query->data)[1] ?? null;
        $transcript = Transcript::find($transcriptId);

        if (! $transcript) {
            \Log::warning("Transcript with ID {$transcriptId} could not be found.");
            return;
        }

        $cacheKey = "summarize:{$transcriptId}";
        if (Cache::has($cacheKey)) {
            return;
        }
        Cache::set($cacheKey, true, now()->addSeconds(30));

        $summary = $this->generateSummary($transcript->text);

        // Send summary
        $this->bot->sendMessage(
            chat_id: $transcript->user_id,
            text: "<b>Zusammenfassung:</b>\n" . $summary,
            parse_mode: 'HTML',
            reply_to_message_id: $transcript->message_id,
        );

        // Remove button
        $this->bot->editMessageReplyMarkup(
            chat_id: $transcript->user_id,
            message_id: $transcript->message_id,
        );

        // Remove transcript data
        $transcript->delete();

        // Answer CallbackQuery
        $this->bot->answerCallbackQuery(
            callback_query_id: $update->callback_query->id,
        );

    }

    protected function generateSummary(string $text): string
    {
        $openai = resolve(Client::class);
        $response = $openai->completions()->create([
            'model'             => 'text-davinci-003',
            'prompt'            => $text . "\n\ntl;dr:",
            'max_tokens'        => 200,
            'temperature'       => 0.7,
            'frequency_penalty' => 0.0,
            'presence_penalty'  => 1.0,
        ]);

        $choice = $response->choices[0];

        if ($choice->finishReason !== 'stop') {
            \Log::warning("Summary finished with reason: {$choice->finishReason}.");
        }

        return trim($choice->text);
    }

}
