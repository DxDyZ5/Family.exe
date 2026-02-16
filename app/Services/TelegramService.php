<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

class TelegramService
{
    protected Api $telegram;

    public function __construct()
    {
        $this->telegram = new Api(config('services.telegram.bot_token'));
    }

    /**
     * Generate a 6-digit OTP, save it on the user, and send via Telegram.
     */
    public function sendOtp(User $user): bool
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'otp_code' => $code,
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        if (!$user->telegram_id) {
            Log::warning("TelegramService: No telegram_id for user {$user->id}");

            return false;
        }

        try {
            $this->telegram->sendMessage([
                'chat_id' => $user->telegram_id,
                'text' => "🌀 *Family VIP Gallery*\n\nYour access code: `{$code}`\n\nExpires in 5 minutes.",
                'parse_mode' => 'Markdown',
            ]);

            Log::info("OTP sent to user {$user->id} via Telegram");

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram OTP failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Verify an OTP code for a user.
     */
    public function verifyOtp(User $user, string $code): bool
    {
        if (!$user->otp_code || !$user->otp_expires_at) {
            return false;
        }

        if ($user->otp_expires_at->isPast()) {
            $user->update(['otp_code' => null, 'otp_expires_at' => null]);

            return false;
        }

        if ($user->otp_code !== $code) {
            return false;
        }

        $user->update(['otp_code' => null, 'otp_expires_at' => null]);

        return true;
    }

    /**
     * Send a message to the admin chat.
     */
    public function notifyAdmin(string $message): void
    {
        $chatId = config('services.telegram.admin_chat_id');

        try {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $e) {
            Log::error('Telegram admin notify failed: ' . $e->getMessage());
        }
    }
}
