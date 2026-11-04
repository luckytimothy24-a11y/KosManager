<?php

namespace App\Services;

use App\Support\WhatsAppNormalizer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    /**
     * Kirim pesan WhatsApp.
     * Jika API tidak dikonfigurasi, pesan di-log saja (no-op).
     */
    public static function send(string $phone, string $message): bool
    {
        $phone = WhatsAppNormalizer::normalize($phone);

        $apiUrl = config('services.whatsapp.api_url');
        $apiToken = config('services.whatsapp.api_token');

        if (empty($apiUrl) || empty($apiToken)) {
            Log::info("WhatsApp (no-op): to {$phone}: {$message}");
            return false;
        }

        try {
            $response = Http::withToken($apiToken)
                ->post($apiUrl, [
                    'phone' => $phone,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                Log::info("WhatsApp sent to {$phone}");
                return true;
            }

            Log::warning("WhatsApp failed to {$phone}: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::warning("WhatsApp exception to {$phone}: " . $e->getMessage());
            return false;
        }
    }
}