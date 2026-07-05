<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Fase3PublicacaoIaController extends Controller
{
    public function index()
    {
        $modulos = [
            ['nome' => 'IA para vídeos de produtos', 'descricao' => 'Simula a criação de vídeos focados em produtos, com destaque para benefícios e chamada de ação.'],
            ['nome' => 'IA para anúncios', 'descricao' => 'Prepara textos, cenas e estrutura para anúncios comerciais.'],
            ['nome' => 'IA para YouTube', 'descricao' => 'Sugere estrutura para vídeos longos, abertura, capítulos e encerramento.'],
            ['nome' => 'IA para Shorts', 'descricao' => 'Simula cortes rápidos e formato vertical para vídeos curtos.'],
            ['nome' => 'IA para Reels', 'descricao' => 'Prepara conteúdo em formato curto para Instagram Reels.'],
            ['nome' => 'IA para TikTok', 'descricao' => 'Sugere ritmo, cortes e estrutura para vídeos de TikTok.'],
            ['nome' => 'Painel de publicação inteligente', 'descricao' => 'Agrupa opções de publicação por plataforma e formato.'],
            ['nome' => 'Homologação da entrega', 'descricao' => 'Confere se a Entrega 4 da Fase 3 está acessível e funcional.'],
        ];

        return view('editor-video.fase3.publicacao-ia', compact('modulos'));
    }

    public function executar(Request $request)
    {
        $acao = $request->input('acao', 'Publicação IA');

        return redirect()
            ->route('editor-video.fase3.publicacao-ia')
            ->with('status', "Recurso '{$acao}' executado em modo de teste. Nenhuma publicação real foi feita ainda.");
    }
}
