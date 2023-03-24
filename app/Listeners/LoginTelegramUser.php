<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Telepath\Events\BeforeHandlingUpdate;

class LoginTelegramUser
{

    public function __construct() {}

    public function handle(BeforeHandlingUpdate $event): void
    {

        $sender = $event->update->user();

        if ($sender) {

            $user = User::updateOrCreate(
                ['id' => $sender->id],
                collect($sender->toArray())
                    ->only(\Schema::getColumnListing((new User)->getTable()))
                    ->all()
            );

            Auth::login($user);

        }

    }

}
