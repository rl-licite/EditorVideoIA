<?php

namespace App\Http\Controllers;

use App\Models\BackupRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BackupController extends Controller
{
    public function index()
    {
        $backups = BackupRecord::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('backups.index', compact('backups'));
    }

    public function store()
    {
        $folder = storage_path('app/backups');

        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        $name = 'backup-editorvideoia-' . now()->format('Ymd-His') . '.zip';
        $path = $folder . DIRECTORY_SEPARATOR . $name;

        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE) !== true) {
            BackupRecord::create([
                'user_id' => Auth::id(),
                'name' => $name,
                'status' => 'erro',
                'notes' => 'Não foi possível criar o arquivo ZIP.',
            ]);

            return redirect()->route('backups.index')->with('error', 'Erro ao criar backup.');
        }

        $databasePath = database_path('database.sqlite');

        if (File::exists($databasePath)) {
            $zip->addFile($databasePath, 'database/database.sqlite');
        }

        $envPath = base_path('.env');

        if (File::exists($envPath)) {
            $zip->addFile($envPath, '.env.backup');
        }

        $zip->close();

        $size = File::exists($path) ? File::size($path) : 0;

        BackupRecord::create([
            'user_id' => Auth::id(),
            'name' => $name,
            'path' => $path,
            'status' => 'criado',
            'size' => $size,
            'notes' => 'Backup local criado com banco SQLite e .env.',
        ]);

        return redirect()->route('backups.index')->with('success', 'Backup criado com sucesso.');
    }
}
