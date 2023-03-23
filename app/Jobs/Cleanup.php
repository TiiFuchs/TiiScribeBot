<?php

namespace App\Jobs;

use App\Models\AudioPipeline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Cleanup implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected AudioPipeline $pipeline,
    ) {}

    public function handle(): void
    {
        $this->pipeline->cleanupFiles();
    }

}
