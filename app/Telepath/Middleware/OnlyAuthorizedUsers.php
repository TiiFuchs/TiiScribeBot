<?php

namespace App\Telepath\Middleware;

use Telepath\Middleware\Middleware;
use Telepath\Telegram\Update;

class OnlyAuthorizedUsers extends Middleware
{

    protected array $authorizedUsers;

    public function __construct() {
        $this->authorizedUsers = str(config('tiiscribe.authorized_users'))
            ->explode(',')
            ->map(fn($item) => trim($item))
            ->filter()
            ->all();
    }

    public function handle(Update $update, callable $next, array $config = [])
    {
        if (! in_array($update->user()?->id, $this->authorizedUsers)) {
            return;
        }

        $next($update);
    }

}
