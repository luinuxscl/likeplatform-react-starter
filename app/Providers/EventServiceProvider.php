<?php

namespace App\Providers;

use App\Listeners\RecordUserLogin;
use App\Listeners\RecordUserLogout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            RecordUserLogin::class,
        ],
        Logout::class => [
            RecordUserLogout::class,
        ],
    ];
}
