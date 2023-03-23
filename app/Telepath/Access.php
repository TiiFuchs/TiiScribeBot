<?php

namespace App\Telepath;

use App\Models\User;
use App\Support\UserSharedRequest;
use App\Telepath\Handlers\UserSharedMessage;
use App\Telepath\Middleware\Can;
use Telepath\Bot;
use Telepath\Handlers\Message\Command;
use Telepath\Middleware\Attributes\Middleware;
use Telepath\Telegram\KeyboardButton;
use Telepath\Telegram\KeyboardButtonRequestUser;
use Telepath\Telegram\ReplyKeyboardMarkup;
use Telepath\Telegram\Update;

#[Middleware(Can::class, 'invite')]
class Access
{

    public function __construct(
        protected Bot $bot,
    ) {}

    #[Command('access')]
    public function access(Update $update)
    {
        $grantAccessButton = KeyboardButton::make(
            text: 'Zugriff gewähren...',
            request_user: KeyboardButtonRequestUser::make(
                request_id: UserSharedRequest::GRANT_ACCESS->value,
                user_is_bot: false,
            )
        );

        $revokeAccessButton = KeyboardButton::make(
            text: 'Zugriff entziehen...',
            request_user: KeyboardButtonRequestUser::make(
                request_id: UserSharedRequest::REVOKE_ACCESS->value,
                user_is_bot: false,
            )
        );

        $this->bot->sendMessage(
            chat_id: $update->message->from->id,
            text: 'Welchem Benutzer willst du Zugriff geben?',
            reply_markup: ReplyKeyboardMarkup::make(
                keyboard: [
                    [$grantAccessButton],
                    [$revokeAccessButton],
                ],
                resize_keyboard: true,
                one_time_keyboard: true,
            )
        );
    }

    #[UserSharedMessage(UserSharedRequest::GRANT_ACCESS)]
    public function grantAccess(Update $update)
    {
        $user = $update->message->user_shared;

        User::updateOrCreate([
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

    #[UserSharedMessage(UserSharedRequest::REVOKE_ACCESS)]
    public function revokeAccess(Update $update)
    {
        $userId = $update->message->user_shared->user_id;

        User::whereId($userId)->update([
            'can_access' => false,
            'can_invite' => false,
        ]);

        $this->bot->sendMessage(
            chat_id: $update->message->from->id,
            text: '🤖 Ich werde nicht mehr auf den ausgewählten Nutzer reagieren.'
        );

    }

}
