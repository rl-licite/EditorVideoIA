<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('video_templates')) {
            return;
        }

        Schema::table('video_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('video_templates', 'subtitle_position')) $table->string('subtitle_position')->default('bottom')->nullable();
            if (!Schema::hasColumn('video_templates', 'subtitle_color')) $table->string('subtitle_color')->default('#ffffff')->nullable();
            if (!Schema::hasColumn('video_templates', 'auto_subtitle')) $table->boolean('auto_subtitle')->default(false);
            if (!Schema::hasColumn('video_templates', 'visual_layout')) $table->json('visual_layout')->nullable();
            if (!Schema::hasColumn('video_templates', 'canvas_width')) $table->integer('canvas_width')->default(1920)->nullable();
            if (!Schema::hasColumn('video_templates', 'canvas_height')) $table->integer('canvas_height')->default(1080)->nullable();
            if (!Schema::hasColumn('video_templates', 'cta_text')) $table->string('cta_text')->nullable();
            if (!Schema::hasColumn('video_templates', 'cta_position')) $table->string('cta_position')->default('bottom')->nullable();
            if (!Schema::hasColumn('video_templates', 'font_family')) $table->string('font_family')->default('Arial')->nullable();
            if (!Schema::hasColumn('video_templates', 'primary_color')) $table->string('primary_color')->default('#22c55e')->nullable();
            if (!Schema::hasColumn('video_templates', 'background_color')) $table->string('background_color')->default('#111827')->nullable();
        });

        if (DB::table('video_templates')->count() === 0) {
            $now = now();
            $templates = [
                [
                    'name' => 'Produto Impacto Horizontal',
                    'format' => 'horizontal',
                    'resolution' => '1920x1080',
                    'overlay_text' => 'Oferta especial para seu produto',
                    'overlay_position' => 'top',
                    'cta_text' => 'COMPRE AGORA',
                    'cta_position' => 'bottom',
                    'watermark_text' => 'RAFAEL',
                    'watermark_position' => 'top-right',
                    'subtitle_position' => 'bottom',
                    'subtitle_color' => '#ffffff',
                    'font_family' => 'Impact',
                    'primary_color' => '#22c55e',
                    'background_color' => '#be185d',
                    'canvas_width' => 1920,
                    'canvas_height' => 1080,
                    'visual_layout' => json_encode(['etapa'=>'3','modelo'=>'produto-horizontal']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Reels Vendas Vertical',
                    'format' => 'vertical',
                    'resolution' => '1080x1920',
                    'overlay_text' => 'Veja todos os detalhes',
                    'overlay_position' => 'top',
                    'cta_text' => 'SAIBA MAIS',
                    'cta_position' => 'bottom',
                    'watermark_text' => '@sua_loja',
                    'watermark_position' => 'bottom-right',
                    'subtitle_position' => 'center',
                    'subtitle_color' => '#ffffff',
                    'font_family' => 'Arial',
                    'primary_color' => '#38bdf8',
                    'background_color' => '#020617',
                    'canvas_width' => 1080,
                    'canvas_height' => 1920,
                    'visual_layout' => json_encode(['etapa'=>'3','modelo'=>'reels-vendas']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Marketplace Quadrado',
                    'format' => 'quadrado',
                    'resolution' => '1080x1080',
                    'overlay_text' => 'Produto em destaque',
                    'overlay_position' => 'center',
                    'cta_text' => 'PEÇA ORÇAMENTO',
                    'cta_position' => 'bottom',
                    'watermark_text' => 'EditorVideoIA',
                    'watermark_position' => 'top-left',
                    'subtitle_position' => 'bottom',
                    'subtitle_color' => '#ffffff',
                    'font_family' => 'Verdana',
                    'primary_color' => '#facc15',
                    'background_color' => '#111827',
                    'canvas_width' => 1080,
                    'canvas_height' => 1080,
                    'visual_layout' => json_encode(['etapa'=>'3','modelo'=>'marketplace-quadrado']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];
            DB::table('video_templates')->insert($templates);
        }
    }

    public function down(): void
    {
        // Nao remove dados do usuario.
    }
};
