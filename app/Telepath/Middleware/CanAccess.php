<?php

namespace App\Telepath\Middleware;

use Illuminate\Support\Facades\Gate;
use Telepath\Middleware\Middleware;
use Telepath\Telegram\Update;

class CanAccess extends Middleware
{

    public function handle(Update $update, callable $next, array $config = [])
    {
        if (Gate::denies('access')) {
            return;
        }

        $next($update);
    }

}
