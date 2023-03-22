<?php

namespace App\Support;

use App\Models\AudioPipeline;
use Telepath\Exceptions\TelegramException;

trait HasStatusMessage
{

    protected AudioPipeline $pipeline;

    protected function setStatus(string $text)
    {
        try {
            \Telepath::bot()->editMessageText(
                ...$this->pipeline->statusMessage(),
                text: $text,
            );
        } catch (TelegramException $e) {
            if (! str_contains($e->getMessage(), 'message is not modified')) {
                throw $e;
            }
        }
    }

}
