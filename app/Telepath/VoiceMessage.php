<?php

namespace App\Telepath;

use App\Jobs\EnhanceAudioJob;
use App\Jobs\TranscribeVoiceMessage;
use App\Models\AudioPipeline;
use App\Telepath\Middleware\Can;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Telepath\Bot;
use Telepath\Handlers\Message\MessageType;
use Telepath\Middleware\Attributes\Middleware;
use Telepath\Telegram\Update;

#[Middleware(Can::class, 'access')]
class VoiceMessage
{

    public function __construct(
        protected Bot $bot,
    ) {}

    #[MessageType(MessageType::VOICE)]
    #[MessageType(MessageType::AUDIO)]
    public function __invoke(Update $update)
    {
        $this->bot->sendMessage(
            $update->message->from->id,
            '💬 Ich verarbeite deine Sprachnachricht...',
        );

        $fileId = $update->message->voice?->file_id
            ?? $update->message->audio?->file_id;

        $filepath = $this->saveFile($fileId);
        
        TranscribeVoiceMessage::dispatch(
            new AudioPipeline(
                $filepath
            ),
            $update->message->chat->id,
        );

//         EnhanceAudioJob::dispatch(
//             new AudioPipeline(
//                 $filepath
//             ),
//             $update->message->chat->id,
//         );
    }

    protected function saveFile(string $fileId): string
    {
        $file = $this->bot->getFile($fileId);

        $token = config('telepath.bots.main.api_token');
        $uri = "https://api.telegram.org/file/bot{$token}/{$file->file_path}";
        $ext = pathinfo($file->file_path, PATHINFO_EXTENSION);

        $filename = 'voice/' . date('YmdHis') . '_' . Str::random() . ".$ext";

        (new Client())->get($uri, [
            'sink' => storage_path("app/$filename"),
        ]);

        return $filename;
    }

}
