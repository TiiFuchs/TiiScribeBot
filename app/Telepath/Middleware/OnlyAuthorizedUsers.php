<?php

namespace App\Telepath\Middleware;

use Telepath\Middleware\Middleware;
use Telepath\Telegram\Update;

class OnlyAuthorizedUsers extends Middleware
{

    protected array $authorizedUsers = [
        397304,
    ];

    public function handle(Update $update, callable $next, array $config = [])
    {
        if (! in_array($update->user()?->id, $this->authorizedUsers)) {
            return;
        }

        $next($update);
    }

}
