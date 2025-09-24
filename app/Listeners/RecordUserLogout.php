<?php

namespace App\Listeners;

use App\Services\Audit\UserSessionRecorder;
use Illuminate\Auth\Events\Logout;

class RecordUserLogout
{
    public function __construct(protected UserSessionRecorder $recorder)
    {
    }

    public function handle(Logout $event): void
    {
        $request = request();
        if (! $request || ! $event->user) {
            return;
        }

        $this->recorder->recordLogout($event->user, $request->session()->getId());
    }
}
