<?php

namespace App\Http\Controllers\EditorVideoIA;

use App\Http\Controllers\Controller;
use App\Models\EditorVideoProject;
use App\Models\MediaAsset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class HealthController extends Controller
{
    public function index()
    {
        $routes = [
            'editor-video.index',
            'editor-video.health',
            'editor-video.media.upload',
            'editor-video.media.delete',
            'editor-video.project.load',
            'editor-video.project.save',
            'templates.index',
        ];

        $routeStatus = [];
        foreach ($routes as $route) {
            $routeStatus[$route] = Route::has($route);
        }

        $tables = [
            'editor_video_projects' => Schema::hasTable('editor_video_projects'),
            'media_assets' => Schema::hasTable('media_assets'),
            'video_templates' => Schema::hasTable('video_templates'),
        ];

        $publicConflict = is_dir(public_path('editor-video'));
        $storageLinked = is_link(public_path('storage')) || file_exists(public_path('storage'));

        return view('editorvideoia.health', [
            'routeStatus' => $routeStatus,
            'tables' => $tables,
            'publicConflict' => $publicConflict,
            'storageLinked' => $storageLinked,
            'projectCount' => $tables['editor_video_projects'] ? EditorVideoProject::count() : 0,
            'assetCount' => $tables['media_assets'] ? MediaAsset::count() : 0,
            'databaseOk' => $this->databaseOk(),
            'etapa' => 'Etapa 1.2 - Blocos 3 e 4',
        ]);
    }

    private function databaseOk(): bool
    {
        try {
            DB::select('select 1');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
