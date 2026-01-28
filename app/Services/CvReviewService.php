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

        // 2️⃣ OpenAI REVIEW
        $response = Http::withToken(config('services.openai.key'))
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a professional HR CV reviewer. Respond ONLY in valid JSON.'
                    ],
                    [
                        'role' => 'user',
                        'content' => "
Analyze this CV and return JSON in EXACT format:

{
  \"issues\": [],
  \"missing_sections\": [],
  \"candidate_email\": \"\",
  \"internal_email\": \"\"
}

Check for missing or weak sections:
- Title / Role
- Email
- Phone Number
- About Me
- Cover Letter
- Education
- Experience
- Skills
- Certifications
- Awards

Rules:
- issues: list of problems found
- missing_sections: sections not present or very weak
- candidate_email: 5–6 lines polite feedback email
- internal_email: 5–6 lines improvement suggestions for HR/admin

CV TEXT:
{$text}
"
                    ]
                ],
                'temperature' => 0.2,
                'max_tokens' => 900
            ]);

        if (!$response->successful()) {
            return ['error' => 'OpenAI request failed'];
        }

        $content = $response->json('choices.0.message.content');

        if (!$content) {
            return ['error' => 'Empty AI response'];
        }

        $data = json_decode($content, true);

        if (!$data) {
            return ['error' => 'Invalid JSON from AI'];
        }

        // 3️⃣ STORE IN DB
        $review = CvReview::create([
            'file_name'       => $filename,
            'file_path'       => $filePath,
            'issues'          => $data['issues'] ?? [],
            'candidate_email' => $data['candidate_email'] ?? '',
            'internal_email'  => $data['internal_email'] ?? '',
        ]);

        return [
            'message'   => 'CV reviewed successfully',
            'review_id' => $review->id,
            'data'      => $data
        ];
    }
}
