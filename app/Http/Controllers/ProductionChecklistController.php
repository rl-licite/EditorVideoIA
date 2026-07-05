<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class ProductionChecklistController extends Controller
{
    public function index()
    {
        $checks = [
            [
                'item' => 'APP_DEBUG desativado em produção',
                'status' => env('APP_DEBUG') ? 'atenção' : 'ok',
                'note' => env('APP_DEBUG') ? 'Em produção deve ficar APP_DEBUG=false.' : 'Configuração segura.',
            ],
            [
                'item' => 'APP_KEY configurada',
                'status' => env('APP_KEY') ? 'ok' : 'erro',
                'note' => env('APP_KEY') ? 'Chave encontrada.' : 'Rode php artisan key:generate.',
            ],
            [
                'item' => 'FFmpeg disponível',
                'status' => trim(shell_exec('ffmpeg -version 2>NUL')) ? 'ok' : 'erro',
                'note' => 'Necessário para renderização.',
            ],
            [
                'item' => 'Storage link',
                'status' => File::exists(public_path('storage')) ? 'ok' : 'atenção',
                'note' => 'Se não existir, rode php artisan storage:link.',
            ],
            [
                'item' => 'Banco SQLite',
                'status' => File::exists(database_path('database.sqlite')) ? 'ok' : 'atenção',
                'note' => 'Para produção maior, considere MySQL/PostgreSQL.',
            ],
        ];

        return view('production.checklist', compact('checks'));
    }
}
