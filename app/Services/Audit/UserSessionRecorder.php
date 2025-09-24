<?php

namespace App\Services\Audit;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UserSessionRecorder
{
    public function recordLogin(User $user, string $sessionId, Request $request): void
    {
        $fingerprint = $this->fingerprint($request->userAgent());

        UserSession::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => $user->getKey(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device' => $fingerprint['device'],
                'platform' => $fingerprint['platform'],
                'browser' => $fingerprint['browser'],
                'login_at' => Carbon::now(),
                'last_activity_at' => Carbon::now(),
                'logout_at' => null,
                'metadata' => [
                    'languages' => $request->getLanguages(),
                    'referer' => $request->headers->get('referer'),
                ],
            ]
        );
    }

    public function recordActivity(User $user, string $sessionId): void
    {
        UserSession::where('session_id', $sessionId)
            ->where('user_id', $user->getKey())
            ->update(['last_activity_at' => Carbon::now()]);
    }

    public function recordLogout(User $user, string $sessionId): void
    {
        UserSession::where('session_id', $sessionId)
            ->where('user_id', $user->getKey())
            ->update(['logout_at' => Carbon::now(), 'last_activity_at' => Carbon::now()]);
    }

    protected function fingerprint(?string $userAgent): array
    {
        $ua = $userAgent ?? '';
        $platform = 'Unknown';
        $browser = 'Unknown';
        $device = 'Desktop';

        $platforms = [
            'Windows' => 'windows',
            'macOS' => 'mac os|macintosh',
            'Linux' => 'linux',
            'Android' => 'android',
            'iOS' => 'iphone|ipad',
        ];

        foreach ($platforms as $name => $pattern) {
            if (preg_match('/'. $pattern .'/i', $ua)) {
                $platform = $name;
                break;
            }
        }

        if (stripos($ua, 'mobile') !== false || stripos($ua, 'android') !== false || stripos($ua, 'iphone') !== false) {
            $device = 'Mobile';
        } elseif (stripos($ua, 'tablet') !== false || stripos($ua, 'ipad') !== false) {
            $device = 'Tablet';
        }

        $browsers = [
            'Edge' => 'edg',
            'Chrome' => 'chrome',
            'Firefox' => 'firefox|fxios',
            'Safari' => 'safari',
            'Opera' => 'opera|opr',
        ];

        foreach ($browsers as $name => $pattern) {
            if (preg_match('/'. $pattern .'/i', $ua)) {
                $browser = $name;
                break;
            }
        }

        return [
            'device' => $device,
            'platform' => $platform,
            'browser' => $browser,
        ];
    }
}
