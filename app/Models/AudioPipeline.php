<?php

namespace App\Models;

use Telepath\Telegram\Message;

class AudioPipeline
{

    public AudioFile $input;

    public ?AudioFile $output = null;

    protected array $statusMessage;

    /**
     * @var AudioFile[]
     */
    protected array $fileHistory;

    public function __construct(string|AudioFile $path, array $fileHistory = [])
    {
        if (! $path instanceof AudioFile) {
            $path = new AudioFile(
                $path,
            );
        }

        $this->input = $path;

        $this->fileHistory = $fileHistory;
    }

    public function setStatusMessage(Message $message): static
    {
        $this->statusMessage = [
            'chat_id'    => $message->chat->id,
            'message_id' => $message->message_id,
        ];

        return $this;
    }

    /**
     * @return array{ chat_id: int, message_id: int }
     */
    public function statusMessage(): array
    {
        return $this->statusMessage;
    }

    public function makeOutput(string $suffix, string $extension = null): AudioFile
    {
        return $this->output = new AudioFile(
            "voice/" . $this->input->derivedName($suffix, $extension)
        );
    }

    public function cleanupFiles(): bool
    {
        $success = true;

        foreach ($this->files() as $file) {
            $success = $success && $file->delete();
        }

        return $success;
    }

    public function pushFile(AudioFile $audioFile): static
    {
        $this->fileHistory[] = $audioFile;

        return $this;
    }

    public function files(): array
    {
        $files = $this->fileHistory;

        $files[] = $this->input;

        if ($this->output) {
            $files[] = $this->output;
        }

        return $files;
    }

    public function nextStep(): static
    {
        return new static(
            $this->output,
            [
                ...$this->fileHistory,
                $this->input,
            ]
        );
    }

}
