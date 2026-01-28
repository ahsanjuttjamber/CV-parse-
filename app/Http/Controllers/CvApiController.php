<?php

namespace App\Http\Controllers;

use App\Services\CvParserService;
use App\Services\CvReviewService;

class CvApiController extends Controller
{
    protected CvParserService $parser;
    protected CvReviewService $reviewService;

    public function __construct(
        CvParserService $parser,
        CvReviewService $reviewService
    ) {
        $this->parser = $parser;
        $this->reviewService = $reviewService;
    }

    // =======================
    // 🔹 PARSE CV (API)
    // =======================
    public function autoParseCv($filename)
    {
        $relativePath = config('cv.storage_path'); // app/public/cv
        $path = storage_path($relativePath . '/' . $filename);

        if (!file_exists($path)) {
            return response()->json([
                'error' => 'CV not found',
                'checked_path' => $path,
            ], 404);
        }

        $result = $this->parser->parseAndStore($path, $filename);

        if (isset($result['error'])) {
            return response()->json($result, 422);
        }

        return response()->json($result, 200);
    }

    // =======================
    // 🔹 REVIEW CV (API)
    // =======================
    public function reviewCv($filename)
    {
        // ✅ Check multiple folders
        $folders = [
            config('cv.review_path'),   // app/public/cvs_to_review
            config('cv.storage_path'),  // app/public/cv
        ];

        $checkedPaths = [];
        $path = null;

        foreach ($folders as $folder) {
            $tryPath = storage_path($folder . '/' . $filename);
            $checkedPaths[] = $tryPath;

            if (file_exists($tryPath)) {
                $path = $tryPath;
                break;
            }
        }

        if (!$path) {
            return response()->json([
                'error' => 'CV not found',
                'checked_paths' => $checkedPaths,
            ], 404);
        }

        $result = $this->reviewService->reviewAndStore($path, $filename);

        if (isset($result['error'])) {
            return response()->json($result, 422);
        }

        return response()->json($result, 200);
    }
}
