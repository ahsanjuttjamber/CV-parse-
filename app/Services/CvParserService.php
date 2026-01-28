<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;
use App\Models\Cv;

class CvParserService
{
    public function parseAndStore(string $filePath, string $filename): array
    {
        // ✅ STEP 1: PDF → TEXT
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        $text = $pdf->getText();

        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $text = trim($text);
        $text = mb_substr($text, 0, 6000);

        // ✅ STEP 2: OpenAI call
        $response = Http::withToken(config('services.openai.key'))
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a CV parser. Respond ONLY with valid JSON.'
                    ],
                    [
                        'role' => 'user',
                        'content' => "
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
                ],
                'temperature' => 0,
                'max_tokens' => 800
            ]);

        if (!$response->successful()) {
            return [
                'error' => 'OpenAI request failed',
                'status' => $response->status()
            ];
        }

        $content = $response->json('choices.0.message.content');

        if (!$content) {
            return ['error' => 'Empty response from OpenAI'];
        }

        try {
            $parsedData = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return [
                'error' => 'Invalid JSON from OpenAI',
                'raw' => $content
            ];
        }

        // ✅ STEP 3: DB SAVE
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
