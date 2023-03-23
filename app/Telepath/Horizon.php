<?php

namespace App\Telepath;

use App\Telepath\Middleware\Can;
use Telepath\Bot;
use Telepath\Handlers\Message\Command;
use Telepath\Middleware\Attributes\Middleware;
use Telepath\Telegram\InlineKeyboardButton;
use Telepath\Telegram\InlineKeyboardMarkup;
use Telepath\Telegram\LoginUrl;
use Telepath\Telegram\Update;

#[Middleware(Can::class, 'viewHorizon')]
class Horizon
{

    public function __construct(
        protected Bot $bot,
    ) {}

    #[Command('horizon')]
    public function horizon(Update $update)
    {
        $horizonButton = InlineKeyboardButton::make(
            text: 'Horizon',
            login_url: LoginUrl::make(
                url: route('login') . '?redirect=' . urlencode(route('horizon.index', [], false)),
            )
        );

        $this->bot->sendMessage(
            chat_id: $update->message->from->id,
            text: 'Zugriff auf Horizon',
            reply_markup: InlineKeyboardMarkup::make(
                [
                    [$horizonButton]
                ]
            )
        );
    }

}
