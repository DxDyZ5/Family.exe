<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the splash/loading screen.
     */
    public function splash(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('gallery');
        }

        return view('loading');
    }

    /**
     * Show the login form.
     */
    public function showLogin(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('gallery');
        }

        return view('login');
    }

    /**
     * Step 1: Check phone number and send OTP via Telegram.
     */
    public function sendOtp(Request $request, TelegramService $telegram): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|min:7',
        ]);

        $phone = preg_replace('/\D/', '', $request->phone);

        $user = User::where('phone_number', $phone)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'phone' => 'Not an authorized VIP. Contact the family admin to get access.',
            ]);
        }

        $sent = $telegram->sendOtp($user);

        if (!$sent) {
            Log::warning("OTP could not be sent for user {$user->id} — no telegram_id");

            return response()->json([
                'success' => false,
                'message' => 'No Telegram account linked. Ask the admin to set your Telegram ID.',
            ], 422);
        }

        session(['auth_phone' => $phone]);

        return response()->json([
            'success' => true,
            'message' => 'Access code sent to your Telegram.',
        ]);
    }

    /**
     * Step 2: Verify OTP and log in permanently.
     */
    public function verifyOtp(Request $request, TelegramService $telegram): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $phone = session('auth_phone');

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Start over.',
            ], 422);
        }

        $user = User::where('phone_number', $phone)->first();

        if (!$user || !$telegram->verifyOtp($user, $request->code)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code. Try again.',
            ], 422);
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        session()->forget('auth_phone');

        Log::info("User {$user->id} authenticated via Telegram OTP");

        return response()->json([
            'success' => true,
            'redirect' => route('gallery'),
        ]);
    }

    /**
     * Resend OTP to the same phone number.
     */
    public function resendOtp(Request $request, TelegramService $telegram): \Illuminate\Http\JsonResponse
    {
        $phone = session('auth_phone');

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Start over.',
            ], 422);
        }

        $user = User::where('phone_number', $phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 422);
        }

        $sent = $telegram->sendOtp($user);

        if (!$sent) {
            return response()->json([
                'success' => false,
                'message' => 'Could not send code. Contact admin.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'New code sent to your Telegram.',
        ]);
    }

    /**
     * Check if the current session is authenticated (used by splash screen).
     */
    public function checkSession(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'authenticated' => Auth::check(),
            'redirect' => Auth::check() ? route('gallery') : route('login'),
        ]);
    }

    /**
     * Logout.
     */
    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('splash');
    }
}
