<?php

namespace App\Listeners;

use App\Services\Audit\UserSessionRecorder;
use Illuminate\Auth\Events\Login;

class RecordUserLogin
{
    public function __construct(protected UserSessionRecorder $recorder)
    {
    }

    public function handle(Login $event): void
    {
        $request = request();
        if (! $request) {
            return;
        }

        $this->recorder->recordLogin($event->user, $request->session()->getId(), $request);
    }
}
