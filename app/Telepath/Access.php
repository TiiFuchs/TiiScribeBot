<?php

namespace App\Telepath;

use App\Models\User;
use App\Support\UserSharedRequest;
use App\Telepath\Handlers\UserSharedMessage;
use App\Telepath\Middleware\CanInvite;
use Telepath\Bot;
use Telepath\Handlers\Message\Command;
use Telepath\Middleware\Attributes\Middleware;
use Telepath\Telegram\KeyboardButton;
use Telepath\Telegram\KeyboardButtonRequestUser;
use Telepath\Telegram\ReplyKeyboardMarkup;
use Telepath\Telegram\Update;

#[Middleware(CanInvite::class)]
class Access
{

    public function __construct(
        protected Bot $bot,
    ) {}

    #[Command('access')]
    public function giveAccess(Update $update)
    {
        $selectUserButton = KeyboardButton::make(
            text: 'Zugriff gewähren...',
            request_user: KeyboardButtonRequestUser::make(
                request_id: UserSharedRequest::ALLOW_USER->value,
                user_is_bot: false,
            )
        );

        $this->bot->sendMessage(
            chat_id: $update->message->from->id,
            text: 'Welchem Benutzer willst du Zugriff geben?',
            reply_markup: ReplyKeyboardMarkup::make(
                keyboard: [
                    [$selectUserButton],
                ],
                resize_keyboard: true,
                one_time_keyboard: true,
            )
        );
    }

    #[UserSharedMessage(UserSharedRequest::ALLOW_USER)]
    public function userSelected(Update $update)
    {
        $user = $update->message->user_shared;

        User::firstOrCreate([
            'id' => $user->user_id,
        ], [
            'is_bot'     => false,
            'first_name' => 'unknown',
            'can_access' => true,
        ]);

        $this->bot->sendMessage(
            chat_id: $update->message->from->id,
            text: '🤖 Ich biete dem ausgewählten Nutzer ab sofort meine Dienste an.',
        );
    }

}
