<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    public function index(Request $request)
    {
        // Check auth
        $this->validateRequest($request);

        $userId = $request->query('id');

        Auth::loginUsingId($userId);
        $request->session()->regenerate();

        if ($redirect = $request->query('redirect')) {
            return redirect(urldecode($redirect));
        } else {
            return redirect('/');
        }
    }

    protected function validateRequest(Request $request): void
    {
        $hash = $request->query('hash');

        $check = collect($request->query())
            ->except(['redirect', 'hash'])
            ->sortKeys()
            ->map(fn($value, $key) => "$key=$value")
            ->implode("\n");

        $key = hash('sha256', config('telepath.bots.main.api_token'), true);

        if (hash_hmac('sha256', $check, $key) !== $hash) {
            abort(403); // Forbidden
        }

        $authDate = Carbon::createFromTimestamp($request->query('auth_date'));
        if ($authDate->addMinutes(10)->isPast()) {
            abort(408); // Request Timeout
        }
    }

}
