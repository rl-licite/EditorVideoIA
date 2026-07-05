<?php

namespace App\Http\Controllers;

use App\Models\AiContent;
use App\Models\BackupRecord;
use App\Models\SystemLog;
use App\Models\Video;
use App\Models\VideoExport;
use App\Models\VideoProject;
use App\Models\VideoTemplate;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'videos' => Video::where('user_id', Auth::id())->count(),
            'templates' => VideoTemplate::where('user_id', Auth::id())->count(),
            'exports' => VideoExport::where('user_id', Auth::id())->count(),
            'concluidos' => VideoExport::where('user_id', Auth::id())->where('status', 'concluido')->count(),
            'erros' => VideoExport::where('user_id', Auth::id())->where('status', 'erro')->count(),
            'ia' => AiContent::where('user_id', Auth::id())->count(),
            'projetos' => VideoProject::where('user_id', Auth::id())->count(),
            'pendentes' => VideoExport::where('user_id', Auth::id())->whereIn('status', ['pendente', 'agendado'])->count(),
            'logs' => SystemLog::where('user_id', Auth::id())->count(),
            'backups' => BackupRecord::where('user_id', Auth::id())->count(),
        ];

        $recentExports = VideoExport::where('user_id', Auth::id())
            ->with(['video', 'template'])
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard', compact('stats', 'recentExports'));
    }
}
