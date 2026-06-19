<?php

namespace App\Services\Agora;

class AgoraService
{
    public function viewerToken(string $channelName, int $uid = 0, int $expireSeconds = 3600): string
    {
        $this->boot();

        return \RtcTokenBuilder2::buildTokenWithUid(
            config('services.agora.app_id'),
            config('services.agora.app_certificate'),
            $channelName,
            $uid,
            \RtcTokenBuilder2::ROLE_SUBSCRIBER,
            $expireSeconds,
            $expireSeconds,
        );
    }

    public function hostToken(string $channelName, int $uid, int $expireSeconds = 3600): string
    {
        $this->boot();

        return \RtcTokenBuilder2::buildTokenWithUid(
            config('services.agora.app_id'),
            config('services.agora.app_certificate'),
            $channelName,
            $uid,
            \RtcTokenBuilder2::ROLE_PUBLISHER,
            $expireSeconds,
            $expireSeconds,
        );
    }

    private function boot(): void
    {
        if (class_exists(\RtcTokenBuilder2::class)) {
            return;
        }

        $previous = getcwd();
        chdir(app_path('Services/Agora/Vendor'));
        require_once 'RtcTokenBuilder2.php';
        chdir($previous);
    }
}
