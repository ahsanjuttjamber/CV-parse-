<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenRouterService
{
    public function chatCompletion(array $payload)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            'Content-Type'  => 'application/json',
        ])->post(env('OPENROUTER_BASE_URL'), $payload);

        if (!$response->successful()) {
            throw new \Exception("OpenRouter API Error: " . $response->body());
        }

        return $response->json();
    }
}
