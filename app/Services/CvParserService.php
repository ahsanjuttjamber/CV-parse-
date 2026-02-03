<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use App\Models\Cv;
use Illuminate\Support\Facades\Http;

class CvParserService
{
    public function parseAndStore(string $filePath, string $filename): array
    {
        // ============================
        // ✅ STEP 1: PDF → TEXT Extract
        // ============================
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);

        $text = mb_convert_encoding(trim($pdf->getText()), 'UTF-8', 'UTF-8');
        $text = mb_substr($text, 0, 6000);

        // ============================
        // ✅ STEP 2: OpenRouter Settings
        // ============================
        $apiKey   = config('openrouter.api_key');
        $baseUrl  = config('openrouter.base_url');
        $model    = config('openrouter.default_model');

        if (!$apiKey || !$baseUrl) {
            return [
                'error' => 'OpenRouter API Key or Base URL missing in config'
            ];
        }

        // ============================
        // ✅ STEP 3: Prompt Messages
        // ============================
        $messages = [
            [
                "role" => "system",
                "content" => "You are a CV parser. Respond ONLY with valid pure JSON. No markdown, no explanation."
            ],
            [
                "role" => "user",
                "content" => "
Return JSON with EXACT keys:

{
  \"title\": \"\",
  \"email\": \"\",
  \"phone_number\": \"\",
  \"cover_letter\": \"\",
  \"about_me\": \"\",
  \"educations\": [],
  \"experiences\": [],
  \"skills\": [],
  \"certifications\": [],
  \"awards\": []
}

CV TEXT:
{$text}
"
            ]
        ];

        // ============================
        // ✅ STEP 4: OpenRouter API Call
        // ============================
        try {
            $response = Http::withToken($apiKey)
                ->withHeaders([
                    "HTTP-Referer" => "http://localhost",
                    "X-Title"      => "Laravel CV Parser"
                ])
                ->post($baseUrl . "/chat/completions", [
                    "model" => $model,
                    "messages" => $messages,
                    "temperature" => 0,
                    "max_tokens" => 800
                ]);

        } catch (\Throwable $e) {
            return [
                'error' => 'OpenRouter request failed: ' . $e->getMessage()
            ];
        }

        if (!$response->successful()) {
            return [
                'error' => 'OpenRouter failed',
                'status' => $response->status(),
                'details' => $response->body()
            ];
        }

        // ============================
        // ✅ STEP 5: Extract Content
        // ============================
        $content = $response->json()['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            return ['error' => 'Empty response from OpenRouter'];
        }

        // ============================
        // ✅ STEP 6: Clean JSON Output
        // ============================
        $content = trim($content);

        // Remove ```json or ``` blocks if AI adds them
        $content = preg_replace('/```json/i', '', $content);
        $content = preg_replace('/```/', '', $content);

        // ============================
        // ✅ STEP 7: Decode JSON Safely
        // ============================
        $parsedData = json_decode($content, true);

        if (!$parsedData) {
            return [
                'error' => 'Invalid JSON from OpenRouter',
                'raw' => $content
            ];
        }

        // ============================
        // ✅ STEP 8: Save to Database
        // ============================
        $cv = Cv::create([
            'file_name'   => $filename,
            'file_path'   => 'storage/cv/' . $filename,
            'parsed_data' => $parsedData
        ]);

        return [
            'success' => true,
            'cv_id'   => $cv->id,
            'data'    => $parsedData
        ];
    }
}
