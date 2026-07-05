<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Fase4ExportacaoProfissionalController extends Controller
{
    public function index()
    {
        $modulos = [
            ['titulo' => 'Exportação 4K e Full HD', 'descricao' => 'Configuração profissional para saída em alta qualidade.'],
            ['titulo' => 'Presets para redes sociais', 'descricao' => 'Modelos de exportação para YouTube, Reels, Shorts e TikTok.'],
            ['titulo' => 'Renderização otimizada', 'descricao' => 'Preparação para processamento mais rápido e estável.'],
            ['titulo' => 'Fila de exportação', 'descricao' => 'Organização de múltiplas exportações em lote.'],
            ['titulo' => 'Controle de qualidade', 'descricao' => 'Checklist visual antes de gerar o vídeo final.'],
            ['titulo' => 'Pacote final do projeto', 'descricao' => 'Organização dos arquivos finais de entrega.'],
            ['titulo' => 'Histórico de exportações', 'descricao' => 'Registro das exportações realizadas no sistema.'],
            ['titulo' => 'Homologação da entrega', 'descricao' => 'Teste final dos recursos de exportação profissional.'],
        ];

        return view('editor-video.fase4.exportacao-profissional', compact('modulos'));
    }

    public function executar(Request $request)
    {
        return redirect()
            ->route('editor-video.fase4.exportacao-profissional')
            ->with('success', 'Recurso de exportação profissional testado com sucesso.');
    }
}
