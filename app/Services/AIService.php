<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    /**
     * Analiza el sentimiento de un comentario en el Diario de Adopción (Alarma Predictiva).
     * Devuelve una puntuación entre -1.00 (muy negativo/alarma) y +1.00 (muy positivo).
     */
    public function analyzeDiarySentiment(string $comment, string $emojiMood): float
    {
        $apiKey = config('services.gemini.key');

        if (!$apiKey) {
            // FALLBACK LOCAL: Análisis básico de palabras clave para pruebas
            return $this->localFallbackSentimentAnalysis($comment, $emojiMood);
        }

        try {
            // Llamada a la API de Gemini (o OpenAI)
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "Analiza el siguiente comentario de un diario de adopción de mascota y su emoji de humor actual: \"{$comment}\" (Emoji: {$emojiMood}). 
                                Devuelve únicamente un número decimal entre -1.00 (que indica arrepentimiento, problemas graves de conducta, infelicidad, peligro de devolución) y +1.00 (que indica felicidad, adaptabilidad completa, cariño, éxito en la adopción). No agregues explicaciones, solo el número decimal."
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $textResult = trim($response->json('candidates.0.content.parts.0.text'));
                if (is_numeric($textResult)) {
                    return (float) $textResult;
                }
            }
        } catch (\Exception $e) {
            Log::error('AI Sentiment Analysis error: ' . $e->getMessage());
        }

        // Si falla la API, usar fallback local
        return $this->localFallbackSentimentAnalysis($comment, $emojiMood);
    }

    /**
     * Escanea una foto subida por el adoptante para buscar señales visuales de maltrato o desnutrición.
     * Devuelve true si se detecta una alerta/alarma (flag de abuso), false si se considera segura.
     */
    public function scanPhotoForAbuse(string $photoPath): bool
    {
        $apiKey = config('services.google_vision.key');

        if (!$apiKey) {
            // FALLBACK LOCAL: Simulación basada en palabras clave del archivo o probabilidad aleatoria controlada
            // Si el nombre del archivo contiene la palabra "test_alert" o "maltrato", gatillamos la alerta
            if (str_contains(strtolower($photoPath), 'alerta') || str_contains(strtolower($photoPath), 'abuso')) {
                return true;
            }
            return false;
        }

        try {
            $imageData = base64_encode(file_get_contents(storage_path('app/' . $photoPath)));

            // Llamada a Google Cloud Vision API (SafeSearch Detection / Label Detection)
            $response = Http::post("https://vision.googleapis.com/v1/images:annotate?key={$apiKey}", [
                'requests' => [
                    [
                        'image' => [
                            'content' => $imageData
                        ],
                        'features' => [
                            ['type' => 'SAFE_SEARCH_DETECTION'],
                            ['type' => 'LABEL_DETECTION', 'maxResults' => 10]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json('responses.0');
                
                // 1. Evaluar SafeSearch (Violencia o contenido sensible)
                $safeSearch = $data['safeSearchAnnotation'] ?? [];
                if (isset($safeSearch['violence']) && in_array($safeSearch['violence'], ['LIKELY', 'VERY_LIKELY'])) {
                    return true;
                }

                // 2. Evaluar etiquetas (ej. si se detectan heridas, desnutrición, suciedad extrema)
                $labels = $data['labelAnnotations'] ?? [];
                foreach ($labels as $label) {
                    $description = strtolower($label['description']);
                    if (str_contains($description, 'wound') || str_contains($description, 'injury') || str_contains($description, 'skin disease')) {
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('AI Photo Scan error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Análisis heurístico local de palabras clave para fallback.
     */
    private function localFallbackSentimentAnalysis(string $comment, string $emojiMood): float
    {
        $score = 0.00;
        $commentLower = strtolower($comment);

        // Palabras que restan puntaje (problemas de conducta, arrepentimiento, quejas)
        $negatives = ['morder', 'romper', 'llorar', 'arrepiento', 'no puedo', 'agresivo', 'destruye', 'dificil', 'complicado', 'cansado', 'triste', 'devolver', 'miedo', 'orina', 'sucio'];
        foreach ($negatives as $word) {
            if (str_contains($commentLower, $word)) {
                $score -= 0.25;
            }
        }

        // Palabras que suman puntaje (felicidad, adaptabilidad, cariño)
        $positives = ['feliz', 'amor', 'lindo', 'juega', 'tranquilo', 'carismático', 'regalon', 'adaptado', 'hermoso', 'bien', 'excelente', 'contento', 'correr', 'pasear'];
        foreach ($positives as $word) {
            if (str_contains($commentLower, $word)) {
                $score += 0.20;
            }
        }

        // Ajustar score base según el emoji
        $emojiMap = [
            '😊' => 0.30,
            '🐕' => 0.10,
            '😐' => 0.00,
            '😞' => -0.30,
            '😡' => -0.50,
        ];
        
        $score += $emojiMap[$emojiMood] ?? 0.00;

        // Limitar entre -1.00 y 1.00
        return max(-1.00, min(1.00, $score));
    }
}
