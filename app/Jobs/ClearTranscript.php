<?php

namespace App\Jobs;

use App\Models\Transcript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ClearTranscript implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deleteWhenMissingModels = true;

    public function __construct(
        protected Transcript $transcript,
    ) {}

    public function handle(): void
    {
        // Remove button
        rescue(function () {
            \Telepath::bot()->editMessageReplyMarkup(
                chat_id: $this->transcript->user_id,
                message_id: $this->transcript->message_id,
            );
        });

        // Remove transcript
        rescue(function () {
            $this->transcript->delete();
        });
    }

}
