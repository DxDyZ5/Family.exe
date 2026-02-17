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

        // 1. Verificación de existencia usando el Driver (más fiable en Railway que path())
        if (!$disk->exists($photo->file_path)) {
            Log::error("ImageModeration: Archivo no encontrado en disco: {$photo->file_path}");
            // Si no hay archivo, no podemos moderar. Devolvemos true para no bloquear por error de disco.
            return false; 
        }

        try {
            // 2. Obtenemos el contenido binario (evita problemas de rutas absolutas en Docker)
            $fileContent = $disk->get($photo->file_path);

            $response = Http::timeout(20)
                ->attach('media', $fileContent, basename($photo->file_path))
                ->post('https://api.sightengine.com/1.0/check.json', [
                    'models' => 'nudity-2.1,offensive,gore',
                    'api_user' => $this->apiUser,
                    'api_secret' => $this->apiSecret,
                ]);

            // 3. Si la API falla por red o servidor (500, 404, etc.)
            if (!$response->successful()) {
                Log::warning('ImageModeration: Fallo de conexión API HTTP ' . $response->status());
                return false; // No castigamos la foto si la API está caída
            }

            $data = $response->json();

            // 4. Si la API responde pero indica un error interno (ej. sin créditos)
            if (($data['status'] ?? '') !== 'success') {
                $errorCode = $data['error']['code'] ?? 'unknown';
                Log::warning("ImageModeration: Error de respuesta API — {$errorCode}");
                return false; 
            }

            // 5. Análisis de resultados con umbrales ajustados para familia
            $nudity = $data['nudity'] ?? [];
            
            // Subimos a 0.8 para evitar falsos positivos en fotos de playa/niños
            $isNude = ($nudity['sexual_activity'] ?? 0) > 0.85
                || ($nudity['sexual_display'] ?? 0) > 0.80
                || ($nudity['erotica'] ?? 0) > 0.80;

            // Offensive suele ser muy sensible, subimos a 0.9
            $isOffensive = ($data['offensive']['prob'] ?? 0) > 0.90;
            $isGore = ($data['gore']['prob'] ?? 0) > 0.75;

            // Log de diagnóstico para que veas qué ve la IA en Railway
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
            // Si el código explota, dejamos pasar la foto.
            return false; 
        }
    }
}
