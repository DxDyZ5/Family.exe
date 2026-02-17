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
     * Returns true if the image is safe (or if a technical error occurs).
     * Returns false ONLY if explicitly flagged by the AI.
     */
    public function moderate(Photo $photo): bool
    {
        $disk = Storage::disk('public');

     
        if (!$disk->exists($photo->file_path)) {
            Log::error("ImageModeration: Archivo no encontrado en disco: {$photo->file_path}");
            
            return true; 
        }

        try {
            $fileContent = $disk->get($photo->file_path);

            $response = Http::timeout(20)
                ->attach('media', $fileContent, basename($photo->file_path))
                ->post('https://api.sightengine.com/1.0/check.json', [
                    'models' => 'nudity-2.1,offensive,gore',
                    'api_user' => $this->apiUser,
                    'api_secret' => $this->apiSecret,
                ]);

       
            if (!$response->successful()) {
                Log::warning('ImageModeration: Fallo de conexión API HTTP ' . $response->status());
                return true; 
            }

            $data = $response->json();

        
            if (($data['status'] ?? '') !== 'success') {
                $errorCode = $data['error']['code'] ?? 'unknown';
                Log::warning("ImageModeration: Error de respuesta API — {$errorCode}");
                return true; 
            }

          
            $nudity = $data['nudity'] ?? [];
            
        
            $isNude = ($nudity['sexual_activity'] ?? 0) > 0.85
                || ($nudity['sexual_display'] ?? 0) > 0.80
                || ($nudity['erotica'] ?? 0) > 0.80;

            // Offensive suele ser muy sensible, subimos a 0.9
            $isOffensive = ($data['offensive']['prob'] ?? 0) > 0.90;
            $isGore = ($data['gore']['prob'] ?? 0) > 0.75;


            Log::info("ImageModeration: Scan Foto {$photo->id}", [
                'scores' => $nudity,
                'is_flagged' => ($isNude || $isOffensive || $isGore)
            ]);

            if ($isNude || $isOffensive || $isGore) {
                Log::info("ImageModeration: Foto {$photo->id} bloqueada por contenido.");
                return false; // BLOQUEO REAL
            }

            return true;

        } catch (\Exception $e) {
            Log::error('ImageModeration: Excepción crítica — ' . $e->getMessage());
  
            return true; 
        }
    }
}
