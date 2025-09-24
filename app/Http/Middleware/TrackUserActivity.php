<?php

namespace App\Http\Middleware;

use App\Services\Audit\UserSessionRecorder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function __construct(protected UserSessionRecorder $recorder)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user()) {
            $sessionId = $request->session()->getId();
            $this->recorder->recordActivity($request->user(), $sessionId);
        }

        return $response;
    }
}
