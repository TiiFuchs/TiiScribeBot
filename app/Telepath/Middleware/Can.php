<?php

namespace App\Telepath\Middleware;

use Illuminate\Support\Facades\Gate;
use Telepath\Telegram\Update;

class Can extends \Telepath\Middleware\Middleware
{

    public function handle(Update $update, callable $next, array $config = [])
    {
        if (Gate::denies($config[0])) {
            return;
        }

        $next($update);
    }

}
