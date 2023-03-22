<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginTelegramUser
{

    public function handle(Request $request, Closure $next)
    {
        $user = new \Telepath\Telegram\User($request->json('message.from'));

        if ($user) {

            Auth::login(
                User::updateOrCreate([
                    'id' => $user->id,
                ], $user->toArray())
            );

        }

        return $next($request);
    }

}
