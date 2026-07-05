<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('editor_video_projects')) {
            Schema::create('editor_video_projects', function (Blueprint $table) {
                $table->id();
                $table->string('name')->default('Projeto de teste');
                $table->json('timeline_data')->nullable();
                $table->integer('duration_seconds')->default(0);
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('editor_video_projects', function (Blueprint $table) {
                if (!Schema::hasColumn('editor_video_projects', 'name')) $table->string('name')->default('Projeto de teste');
                if (!Schema::hasColumn('editor_video_projects', 'timeline_data')) $table->json('timeline_data')->nullable();
                if (!Schema::hasColumn('editor_video_projects', 'duration_seconds')) $table->integer('duration_seconds')->default(0);
                if (!Schema::hasColumn('editor_video_projects', 'settings')) $table->json('settings')->nullable();
            });
        }

        if (!Schema::hasTable('media_assets')) {
            Schema::create('media_assets', function (Blueprint $table) {
                $table->id();
                $table->string('original_name');
                $table->string('stored_name');
                $table->string('mime_type')->nullable();
                $table->string('media_type', 30)->default('other');
                $table->string('extension', 20)->nullable();
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->decimal('duration_seconds', 10, 2)->nullable();
                $table->unsignedInteger('width')->nullable();
                $table->unsignedInteger('height')->nullable();
                $table->string('storage_path');
                $table->string('public_url');
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('video_templates')) {
            Schema::create('video_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('format')->default('vertical');
                $table->string('resolution')->default('1080x1920');
                $table->string('watermark_text')->nullable();
                $table->string('watermark_position')->default('bottom-right');
                $table->string('overlay_text')->nullable();
                $table->string('overlay_position')->default('bottom');
                $table->integer('clip_start')->default(0);
                $table->integer('clip_duration')->nullable();
                $table->string('music_path')->nullable();
                $table->string('intro_path')->nullable();
                $table->string('outro_path')->nullable();
                $table->boolean('auto_subtitle')->default(false);
                $table->string('subtitle_position')->nullable();
                $table->string('subtitle_color')->nullable();
                $table->string('cta_text')->nullable();
                $table->string('cta_position')->nullable();
                $table->string('font_family')->nullable();
                $table->string('primary_color')->nullable();
                $table->string('background_color')->nullable();
                $table->json('visual_layout')->nullable();
                $table->integer('canvas_width')->default(1080);
                $table->integer('canvas_height')->default(1920);
                $table->timestamps();
            });
        } else {
            Schema::table('video_templates', function (Blueprint $table) {
                foreach ([
                    'auto_subtitle' => 'boolean', 'subtitle_position' => 'string', 'subtitle_color' => 'string',
                    'cta_text' => 'string', 'cta_position' => 'string', 'font_family' => 'string',
                    'primary_color' => 'string', 'background_color' => 'string', 'visual_layout' => 'json',
                    'canvas_width' => 'integer', 'canvas_height' => 'integer'
                ] as $column => $type) {
                    if (!Schema::hasColumn('video_templates', $column)) {
                        $field = $table->{$type}($column)->nullable();
                        if (in_array($column, ['canvas_width','canvas_height'])) $field->default($column === 'canvas_width' ? 1080 : 1920);
                        if ($column === 'auto_subtitle') $field->default(false);
                    }
                }
            });
        }
    }

    public function down(): void {}
};
