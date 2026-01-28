<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CvParserService;
use App\Services\CvReviewService;

class ProcessCvCommand extends Command
{
    protected $signature = 'cv:process {mode : parse|review} {filename}';
    protected $description = 'Parse or Review CV using AI';

    protected CvParserService $parser;
    protected CvReviewService $reviewService;

    public function __construct(
        CvParserService $parser,
        CvReviewService $reviewService
    ) {
        parent::__construct();
        $this->parser = $parser;
        $this->reviewService = $reviewService;
    }

    public function handle()
    {
        $mode = $this->argument('mode');
        $filename = $this->argument('filename');

        if (!in_array($mode, ['parse', 'review'])) {
            $this->error('❌ Invalid mode. Use parse or review');
            return Command::FAILURE;
        }

        // ✅ Select path by mode
        $relativePath = $mode === 'parse'
            ? config('cv.storage_path')
            : config('cv.review_path');

        $path = storage_path($relativePath . '/' . $filename);

        if (!file_exists($path)) {
            $this->error('❌ CV not found at: ' . $path);
            return Command::FAILURE;
        }

        // ✅ Execute correct service
        $result = $mode === 'parse'
            ? $this->parser->parseAndStore($path, $filename)
            : $this->reviewService->reviewAndStore($path, $filename);

        if (isset($result['error'])) {
            $this->error($result['error']);
            return Command::FAILURE;
        }

        $this->info('✅ CV ' . ucfirst($mode) . ' completed successfully');

        return Command::SUCCESS;
    }
}
