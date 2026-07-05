<?php

namespace App\Services;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;

class SystemLogService
{
    public function info(string $area, string $message, array $context = []): void
    {
        $this->log('info', $area, $message, $context);
    }

    public function error(string $area, string $message, array $context = []): void
    {
        $this->log('error', $area, $message, $context);
    }

    public function warning(string $area, string $message, array $context = []): void
    {
        $this->log('warning', $area, $message, $context);
    }

    private function log(string $level, string $area, string $message, array $context = []): void
    {
        SystemLog::create([
            'user_id' => Auth::id(),
            'level' => $level,
            'area' => $area,
            'message' => $message,
            'context' => $context,
        ]);
    }
}
