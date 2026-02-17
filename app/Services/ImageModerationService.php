<?php
//HELLO
namespace App\Services;

use App\Models\Photo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageModerationService
{
    protected string $apiUser;

    protected string $apiSecret;

    public function __construct()
    {
        $this->apiUser = config('services.sightengine.user');
        $this->apiSecret = config('services.sightengine.secret');
    }

    /**
     * Moderate an uploaded photo via Sightengine.
     * Returns true if the image is safe, false if flagged or API fails.
     */
    public function moderate(Photo $photo): bool
    {
        $filePath = Storage::disk('public')->path($photo->file_path);

        if (!file_exists($filePath)) {
            Log::error("ImageModeration: File not found at {$filePath}");

            return false;
        }

        try {
            Log::info('ImageModeration: Sending request to Sightengine', ['file' => $filePath]);

            $response = Http::timeout(15)
                ->attach('media', file_get_contents($filePath), basename($filePath))
                ->post('https://api.sightengine.com/1.0/check.json', [
                    'models' => 'nudity-2.1,offensive,gore',
                    'api_user' => $this->apiUser,
                    'api_secret' => $this->apiSecret,
                ]);

            if (!$response->successful()) {
                Log::warning('ImageModeration: API returned HTTP ' . $response->status());

                return false;
            }

            $data = $response->json();

            if (($data['status'] ?? '') !== 'success') {
                $errorCode = $data['error']['code'] ?? 'unknown';
                Log::warning("ImageModeration: API error — {$errorCode}");

                return false;
            }

            $nudity = $data['nudity'] ?? [];
            $isNude = ($nudity['sexual_activity'] ?? 0) > 0.5
                || ($nudity['sexual_display'] ?? 0) > 0.5
                || ($nudity['erotica'] ?? 0) > 0.5;

            $isOffensive = ($data['offensive']['prob'] ?? 0) > 0.7;
            $isGore = ($data['gore']['prob'] ?? 0) > 0.5;

            if ($isNude || $isOffensive || $isGore) {
                Log::info("ImageModeration: Photo {$photo->id} flagged (nude={$isNude}, offensive={$isOffensive}, gore={$isGore})");

                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('ImageModeration: Exception — ' . $e->getMessage());

            return false;
        }
    }
}
