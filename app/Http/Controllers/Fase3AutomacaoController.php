<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Fase3AutomacaoController extends Controller
{
    public function index()
    {
        $modulos = [
            ['nome' => 'IA para corte automático', 'descricao' => 'Simula a análise do vídeo para sugerir pontos de corte.'],
            ['nome' => 'IA para remover silêncios', 'descricao' => 'Prepara a automação para identificar trechos sem fala.'],
            ['nome' => 'IA para momentos importantes', 'descricao' => 'Marca possíveis trechos fortes do vídeo.'],
            ['nome' => 'Enquadramento automático', 'descricao' => 'Centraliza o assunto principal do vídeo.'],
            ['nome' => 'Zoom inteligente', 'descricao' => 'Sugere aproximações em momentos relevantes.'],
            ['nome' => 'Reposicionamento automático', 'descricao' => 'Ajusta posição visual de mídia na tela.'],
            ['nome' => 'Painel de automações', 'descricao' => 'Agrupa as ações de IA em uma tela simples.'],
            ['nome' => 'Homologação da entrega', 'descricao' => 'Confere se a Entrega 1 da Fase 3 está acessível.'],
        ];

        return view('editor-video.fase3.automacoes', compact('modulos'));
    }

    public function executar(Request $request)
    {
        $acao = $request->input('acao', 'automacao');

        return back()->with('fase3_ok', "Automação '{$acao}' executada em modo de teste. Nenhum vídeo real foi alterado ainda.");
    }
}
