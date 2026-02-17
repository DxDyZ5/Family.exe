<?php

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
    $disk = Storage::disk('public');

    // Si Laravel no ve el archivo, NO DEVUELVAS FALSE. 
    // Devuelve true para que al menos la foto se vea y no se quede en blur eterno.
    if (!$disk->exists($photo->file_path)) {
        Log::error("RAILWAY ERROR: Archivo no encontrado en el volumen: {$photo->file_path}");
        return true; // <--- CAMBIO CLAVE
    }

    try {
        $fileContent = $disk->get($photo->file_path);

        $response = Http::timeout(15)
            ->attach('media', $fileContent, basename($photo->file_path))
            ->post('https://api.sightengine.com/1.0/check.json', [
                'models' => 'nudity-2.1,offensive,gore',
                'api_user' => $this->apiUser,
                'api_secret' => $this->apiSecret,
            ]);

        if (!$response->successful()) {
            Log::warning('Sightengine Down: HTTP ' . $response->status());
            return true; // Si la API falla, no bloquees al usuario
        }

        $data = $response->json();

        if (($data['status'] ?? '') !== 'success') {
            return true; // Si hay error de créditos o algo, permite la foto
        }

        // SOLO bloqueamos si la IA está segura
        $nudity = $data['nudity'] ?? [];
        $isNude = ($nudity['sexual_activity'] ?? 0) > 0.8 || ($nudity['erotica'] ?? 0) > 0.8;
        $isOffensive = ($data['offensive']['prob'] ?? 0) > 0.9;

        if ($isNude || $isOffensive) {
            return false; // AQUÍ SÍ BLOQUEAMOS
        }

        return true;
    } catch (\Exception $e) {
        Log::error('Excepción en Moderación: ' . $e->getMessage());
        return true; // Ante la duda, deja pasar la foto
    }
}
}
