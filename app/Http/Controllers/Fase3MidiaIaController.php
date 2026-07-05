<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Fase3MidiaIaController extends Controller
{
    public function index()
    {
        $modulos = [
            ['nome' => 'Legendas automáticas por IA', 'descricao' => 'Simula a criação de legendas a partir do áudio do vídeo.'],
            ['nome' => 'Tradução automática', 'descricao' => 'Prepara o fluxo para traduzir legendas e textos do projeto.'],
            ['nome' => 'Dublagem por IA', 'descricao' => 'Simula a criação de uma nova voz para outro idioma.'],
            ['nome' => 'Sincronização labial', 'descricao' => 'Prepara a etapa para alinhar fala, boca e áudio.'],
            ['nome' => 'Correção automática de áudio', 'descricao' => 'Simula limpeza de ruído, volume e melhora da fala.'],
            ['nome' => 'Geração de narração', 'descricao' => 'Prepara narração automática para vídeos e tutoriais.'],
            ['nome' => 'Exportação inteligente', 'descricao' => 'Sugere formatos conforme destino: YouTube, Reels, Shorts ou TikTok.'],
            ['nome' => 'Homologação da entrega', 'descricao' => 'Confere se a Entrega 2 da Fase 3 está acessível e funcional.'],
        ];

        return view('editor-video.fase3.midia-ia', compact('modulos'));
    }

    public function executar(Request $request)
    {
        $acao = $request->input('acao', 'Automação IA');
        return redirect()
            ->route('editor-video.fase3.midia-ia')
            ->with('sucesso', "Recurso '{$acao}' executado em modo de teste. Nenhum vídeo real foi alterado ainda.");
    }
}
