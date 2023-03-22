<?php

namespace App\Telepath\Handlers;

use App\Support\UserSharedRequest;
use Telepath\Bot;
use Telepath\Handlers\Message\MessageType;
use Telepath\Telegram\Update;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class UserSharedMessage extends MessageType
{

    public function __construct(protected UserSharedRequest $request)
    {
        parent::__construct(MessageType::USER_SHARED);
    }

    public function responsible(Bot $bot, Update $update): bool
    {
        return parent::responsible($bot, $update)
            && $update->message->user_shared->request_id === $this->request->value;
    }

}
