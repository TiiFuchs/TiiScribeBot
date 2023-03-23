<?php

namespace App\Telepath;

use App\Telepath\Middleware\Can;
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

    #[CallbackQueryData(exact: 'summarize')]
    public function summarize(Update $update)
    {
        $text = $update->callback_query->message->text;
        $summary = $this->generateSummary($text);

        // Send summary
        $this->bot->sendMessage(
            chat_id: $update->callback_query->message->chat->id,
            reply_to_message_id: $update->callback_query->message->message_id,
            text: "<b>Zusammenfassung:</b>\n\n" . $summary,
            parse_mode: 'HTML',
        );

        // Remove button
        $this->bot->editMessageReplyMarkup(
            chat_id: $update->callback_query->message->chat->id,
            message_id: $update->callback_query->message->message_id,
            reply_markup: InlineKeyboardMarkup::make(
                [[]]
            )
        );

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
