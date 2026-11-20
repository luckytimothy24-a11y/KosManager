<?php

namespace App\Services;

use App\Models\UserDevice;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    private ?WebPush $webPush = null;

    public function __construct()
    {
        $publicKey = config('services.vapid.public_key', env('VAPID_PUBLIC_KEY'));
        $privateKey = config('services.vapid.private_key', env('VAPID_PRIVATE_KEY'));
        $subject = config('services.vapid.subject', env('VAPID_SUBJECT', 'mailto:admin@kosmanager.test'));

        if ($publicKey && $privateKey) {
            $this->webPush = new WebPush([
                'VAPID' => [
                    'subject' => $subject,
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ]);
        }
    }

    public function isAvailable(): bool
    {
        return $this->webPush !== null;
    }

    public function sendToUser(int $userId, string $title, string $body, ?string $url = null, ?array $data = null): void
    {
        $devices = UserDevice::where('user_id', $userId)
            ->where('is_active', true)
            ->whereNotNull('subscription_data')
            ->get();

        foreach ($devices as $device) {
            $this->sendToDevice($device, $title, $body, $url, $data);
        }
    }

    public function sendToDevice(UserDevice $device, string $title, string $body, ?string $url = null, ?array $data = null): void
    {
        if (! $this->webPush || ! $device->subscription_data) {
            return;
        }

        try {
            $subscription = Subscription::create($device->subscription_data);
            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'icon' => '/icons/icon-192x192.png',
                'badge' => '/icons/icon-72x72.png',
                'url' => $url ?? '/dashboard',
                'data' => $data,
            ]);

            $this->webPush->queueNotification($subscription, $payload);
            $this->webPush->flush();

            $device->update(['last_active_at' => now()]);
        } catch (\Exception $e) {
            Log::warning('Push notification failed for device '.$device->id.': '.$e->getMessage());

            // Mark device as inactive if subscription expired
            if (str_contains($e->getMessage(), '410') || str_contains($e->getMessage(), 'expired')) {
                $device->update(['is_active' => false]);
            }
        }
    }

    public function sendToUsers(array $userIds, string $title, string $body, ?string $url = null, ?array $data = null): void
    {
        foreach ($userIds as $userId) {
            $this->sendToUser($userId, $title, $body, $url, $data);
        }
    }
}
