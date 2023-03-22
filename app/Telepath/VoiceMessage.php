<?php

namespace App\Telepath;

use App\Jobs\EnhanceAudioJob;
use App\Models\AudioPipeline;
use App\Telepath\Middleware\OnlyAuthorizedUsers;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Telepath\Bot;
use Telepath\Handlers\Message\MessageType;
use Telepath\Middleware\Attributes\Middleware;
use Telepath\Telegram\Update;
use Telepath\Telegram\Voice;

#[Middleware(OnlyAuthorizedUsers::class)]
class VoiceMessage
{

    public function __construct(
        protected Bot $bot,
    ) {}

    #[MessageType(MessageType::VOICE)]
    public function __invoke(Update $update)
    {
        $this->bot->sendMessage(
            $update->message->from->id,
            '💬 Ich verarbeite deine Sprachnachricht...',
        );

        $filepath = $this->saveFile($update->message->voice);

        EnhanceAudioJob::dispatch(
            new AudioPipeline(
                $filepath
            ),
            $update->message->chat->id,
        );
    }

    protected function saveFile(Voice $voice): string
    {
        $file = $this->bot->getFile($voice->file_id);

        $token = config('telepath.bots.main.api_token');
        $uri = "https://api.telegram.org/file/bot{$token}/{$file->file_path}";

        $filename = 'voice/' . date('YmdHis') . '_' . Str::random() . '.oga';

        (new Client())->get($uri, [
            'sink' => storage_path("app/$filename"),
        ]);

        return $filename;
    }

}
