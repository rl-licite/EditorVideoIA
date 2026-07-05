<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('video_templates', 'visual_layout')) {
                $table->json('visual_layout')->nullable();
            }
            if (!Schema::hasColumn('video_templates', 'canvas_width')) {
                $table->integer('canvas_width')->default(1080);
            }
            if (!Schema::hasColumn('video_templates', 'canvas_height')) {
                $table->integer('canvas_height')->default(1920);
            }
        });
    }

    public function down(): void
    {
        Schema::table('video_templates', function (Blueprint $table) {
            foreach (['visual_layout', 'canvas_width', 'canvas_height'] as $column) {
                if (Schema::hasColumn('video_templates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
