<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('video_templates', 'cta_text')) $table->string('cta_text', 140)->nullable();
            if (!Schema::hasColumn('video_templates', 'cta_position')) $table->string('cta_position', 30)->default('bottom');
            if (!Schema::hasColumn('video_templates', 'cta_style')) $table->string('cta_style', 30)->default('button');
            if (!Schema::hasColumn('video_templates', 'cta_start_second')) $table->integer('cta_start_second')->default(0);
            if (!Schema::hasColumn('video_templates', 'cta_end_second')) $table->integer('cta_end_second')->default(0);
            if (!Schema::hasColumn('video_templates', 'background_color')) $table->string('background_color', 20)->default('#0f172a');
            if (!Schema::hasColumn('video_templates', 'font_family')) $table->string('font_family', 60)->default('Arial');
            if (!Schema::hasColumn('video_templates', 'primary_color')) $table->string('primary_color', 20)->default('#facc15');
        });
    }

    public function down(): void
    {
        Schema::table('video_templates', function (Blueprint $table) {
            foreach (['cta_text','cta_position','cta_style','cta_start_second','cta_end_second','background_color','font_family','primary_color'] as $column) {
                if (Schema::hasColumn('video_templates', $column)) $table->dropColumn($column);
            }
        });
    }
};
