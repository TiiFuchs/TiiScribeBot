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
        $from = $this->findSender($request);

        if ($from) {

            Auth::login(
                User::updateOrCreate(
                    [
                        'id' => $from['id'],
                    ],
                    collect($from)->only([
                        'is_bot',
                        'first_name',
                        'last_name',
                        'username',
                        'language_code',
                        'is_premium',
                        'added_to_attachment_menu',
                    ])->all()
                )
            );

        }

        return $next($request);
    }

    protected function findSender(Request $request): ?array
    {
        foreach ($request->json() as $type => $data) {

            if (is_array($data) && isset($data['from'])) {
                return $data['from'];
            }

        }

        return null;
    }

}
