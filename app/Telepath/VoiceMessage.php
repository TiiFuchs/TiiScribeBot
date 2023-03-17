<?php

namespace App\Telepath;

use App\Jobs\ConvertFiletype;
use Illuminate\Support\Str;
use Telepath\Bot;
use Telepath\Handlers\Message\MessageType;
use Telepath\Telegram\Update;
use Telepath\Telegram\Voice;

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
            '🔊 Ich verarbeite deine Sprachnachricht...',
        );

        $filename = $this->saveFile($update->message->voice);

        ConvertFiletype::dispatch(
            $update->message->from->id,
            $filename,
        );
    }

    protected function saveFile(Voice $voice): string
    {
        $file = $this->bot->getFile($voice->file_id);

        $token = config('telepath.bots.main.api_token');
        $uri = "https://api.telegram.org/file/bot{$token}/{$file->file_path}";

        $filename = date('YmdHis') . '_' . Str::random() . '.oga';
        $target = fopen(storage_path("app/voice/$filename"), 'wb');

        $curl = curl_init($uri);
        curl_setopt_array($curl, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FILE           => $target,
        ]);
        curl_exec($curl);
        curl_close($curl);

        fclose($target);

        return $filename;
    }

}
