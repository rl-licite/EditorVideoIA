<?php

namespace App\Jobs;

use App\Models\VideoExport;
use App\Services\VideoRenderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessVideoExportJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1200;

    public function __construct(public int $exportId)
    {
    }

    public function handle(VideoRenderService $renderService): void
    {
        $export = VideoExport::find($this->exportId);

        if (!$export) {
            return;
        }

        $renderService->render($export);
    }
}
