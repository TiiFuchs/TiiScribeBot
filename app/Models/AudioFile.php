<?php

namespace App\Models;

class AudioFile
{

    public function __construct(
        public readonly string $path,
    ) {}

    public function fullPath(): string
    {
        return storage_path("app/{$this->path}");
    }

    public function dir(): string
    {
        return dirname($this->path);
    }

    public function name(): string
    {
        return basename($this->path);
    }

    public function derivedName(string $suffix, string $extension = null): string
    {
        $info = new \SplFileInfo($this->path);

        $ext = $info->getExtension();
        $basename = $info->getBasename(".$ext");

        if ($extension) {
            $ext = $extension;
        }

        return "{$basename}-{$suffix}.$ext";
    }

    public function exists(): bool
    {
        return file_exists($this->fullPath());
    }

    /**
     * @return false|resource
     */
    public function read()
    {
        return fopen($this->fullPath(), 'rb');
    }

    /**
     * @return false|resource
     */
    public function write()
    {
        return fopen($this->fullPath(), 'wb');
    }

    public function delete()
    {
        unlink($this->fullPath());
    }

}
