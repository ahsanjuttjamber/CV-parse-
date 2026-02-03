<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;
use App\Models\CvReview;

class CvReviewService
{
    public function reviewAndStore(string $filePath, string $filename): array
    {
        // 1️⃣ PDF → TEXT
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);

        $text = trim($pdf->getText());
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $text = mb_substr($text, 0, 6000);

        // 2️⃣ OpenRouter Config
        $baseUrl = config('openrouter.base_url');
        $apiKey  = config('openrouter.api_key');
        $model   = config('openrouter.default_model');

        try {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post($baseUrl . '/chat/completions', [

                    'model' => $model,

                    'messages' => [

                        [
                            'role' => 'system',
                            'content' =>
                                "You are a professional HR CV reviewer.
Return ONLY raw JSON.
Do NOT use markdown.
Do NOT wrap inside ```json.
Candidate_email and internal_email MUST be plain strings."
                        ],

                        [
                            'role' => 'user',
                            'content' => <<<PROMPT
Return JSON ONLY in this format:

{
  "title": "CV Review",
  "system": [
    "Analyzes the CV",
    "Displays detected issues",
    "Shows two generated email contents:",
    "1. Candidate feedback email",
    "2. Internal improvement email"
  ],
  "message": "CV reviewed successfully",
  "review_id": 7,
  "data": {
    "issues": [],
    "missing_sections": [],
    "candidate_email": "",
    "internal_email": ""
  }
}

Rules:
- issues must be simple strings (not objects)
- candidate_email must be 5–6 line string
- internal_email must be 5–6 line string

CV TEXT:
{$text}
PROMPT
                        ]
                    ],

                    'temperature' => 0.2,
                    'max_tokens' => 900
                ]);

        } catch (\Throwable $e) {
            return ['error' => 'OpenRouter request failed: ' . $e->getMessage()];
        }

        if (!$response->successful()) {
            return ['error' => 'OpenRouter failed', 'status' => $response->status()];
        }

        $content = $response->json('choices.0.message.content');

        if (!$content) {
            return ['error' => 'Empty response from OpenRouter'];
        }

        // ✅ Remove Markdown Wrapper
        $content = preg_replace('/```json|```/', '', $content);
        $content = trim($content);

        // ✅ Decode JSON safely
        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return [
                'error' => 'Invalid JSON from OpenRouter',
                'raw' => $content
            ];
        }

        // ✅ Force emails into strings
        if (is_array($data['candidate_email'] ?? null)) {
            $data['candidate_email'] = implode("\n", $data['candidate_email']['body'] ?? []);
        }

        if (is_array($data['internal_email'] ?? null)) {
            $data['internal_email'] = implode("\n", $data['internal_email']['body'] ?? []);
        }

        // 3️⃣ Store in DB
        $review = CvReview::create([
            'file_name'        => $filename,
            'file_path'        => $filePath,
            'issues'           => $data['issues'] ?? [],
            'missing_sections' => $data['missing_sections'] ?? [],
            'candidate_email'  => $data['candidate_email'] ?? '',
            'internal_email'   => $data['internal_email'] ?? '',
        ]);

        return [
            'title' => 'CV Review',
            'system' => [
                'Analyzes the CV',
                'Displays detected issues',
                'Shows two generated email contents:',
                '1. Candidate feedback email',
                '2. Internal improvement email'
            ],

            'message'   => 'CV reviewed successfully',
            'review_id' => $review->id,

            'data'      => $data
        ];
    }
}
