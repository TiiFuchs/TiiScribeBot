<?php

namespace App\Telepath;

use App\Telepath\Middleware\CanAccess;
use Telepath\Bot;
use Telepath\Handlers\Message\Command;
use Telepath\Middleware\Attributes\Middleware;
use Telepath\Telegram\Update;

#[Middleware(CanAccess::class)]
class Start
{

    public function __construct(
        protected Bot $bot,
    ) {}

    #[Command('start')]
    public function __invoke(Update $update)
    {
        $name = $update->message->from->first_name;

        $this->bot->sendMessage(
            chat_id: $update->message->chat->id,
            text: <<<EOT
👋 Hallo, $name.
Ich stehe dir gerne zur Verfügung.
💬 Leite mir dazu einfach eine Sprachnachricht weiter und ich transkribiere sie für dich.
🚗 🏗️ 🔊 Störende Hintergrundgeräusche entferne ich dabei auch gleich mit.

⚠️ 💰 Aber bitte behalte im Kopf, dass jede Minute Geld kostet.
EOT
        );
    }

}
