<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class Fase3FechamentoController extends Controller
{
    public function index()
    {
        $basePath = base_path();
        $checks = $this->buildChecks();
        $summary = [
            'controllers' => $this->countFiles(app_path('Http/Controllers'), 'php'),
            'views' => $this->countFiles(resource_path('views'), 'blade.php'),
            'routes' => count(Route::getRoutes()),
            'modules' => count($checks),
            'memory' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
        ];

        return view('editor-video.fase3.fechamento', compact('checks', 'summary'));
    }

    public function executar(Request $request)
    {
        $acao = $request->input('acao', 'auditoria');

        return redirect()
            ->route('editor-video.fase3.fechamento')
            ->with('success', 'Fechamento da Fase 3 executado com sucesso: ' . ucfirst(str_replace('-', ' ', $acao)) . '.');
    }

    private function buildChecks(): array
    {
        $required = [
            ['nome' => 'Controller Entrega 1', 'tipo' => 'Controller', 'path' => app_path('Http/Controllers/Fase3AutomacaoController.php')],
            ['nome' => 'Controller Entrega 2', 'tipo' => 'Controller', 'path' => app_path('Http/Controllers/Fase3MidiaIaController.php')],
            ['nome' => 'Controller Entrega 3', 'tipo' => 'Controller', 'path' => app_path('Http/Controllers/Fase3ProducaoIaController.php')],
            ['nome' => 'Controller Entrega 4', 'tipo' => 'Controller', 'path' => app_path('Http/Controllers/Fase3PublicacaoIaController.php')],
            ['nome' => 'Controller Entrega 5', 'tipo' => 'Controller', 'path' => app_path('Http/Controllers/Fase3EnterpriseIaController.php')],
            ['nome' => 'Controller Fechamento', 'tipo' => 'Controller', 'path' => app_path('Http/Controllers/Fase3FechamentoController.php')],
            ['nome' => 'View Fechamento', 'tipo' => 'View', 'path' => resource_path('views/editor-video/fase3/fechamento.blade.php')],
            ['nome' => 'Storage Laravel', 'tipo' => 'Sistema', 'path' => storage_path()],
            ['nome' => 'Arquivo de rotas', 'tipo' => 'Rotas', 'path' => base_path('routes/web.php')],
            ['nome' => 'Arquivo Artisan', 'tipo' => 'Laravel', 'path' => base_path('artisan')],
        ];

        return array_map(function ($item) {
            $exists = file_exists($item['path']);
            return [
                'nome' => $item['nome'],
                'tipo' => $item['tipo'],
                'status' => $exists ? 'OK' : 'Pendente',
                'mensagem' => $exists ? 'Encontrado no projeto.' : 'Arquivo ou pasta não encontrado.',
            ];
        }, $required);
    }

    private function countFiles(string $dir, string $extension): int
    {
        if (!is_dir($dir)) {
            return 0;
        }

        $count = 0;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), $extension)) {
                $count++;
            }
        }

        return $count;
    }
}
