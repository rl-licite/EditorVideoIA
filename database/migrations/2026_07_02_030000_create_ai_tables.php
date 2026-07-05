<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('video_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('video_subtitles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('video_id')->nullable()->constrained()->nullOnDelete();
            $table->text('text');
            $table->integer('start_second')->default(0);
            $table->integer('end_second')->default(3);
            $table->timestamps();
        });

        Schema::table('video_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('video_templates', 'auto_subtitle')) {
                $table->boolean('auto_subtitle')->default(false);
            }

            if (!Schema::hasColumn('video_templates', 'subtitle_position')) {
                $table->string('subtitle_position')->default('bottom');
            }

            if (!Schema::hasColumn('video_templates', 'subtitle_color')) {
                $table->string('subtitle_color')->default('white');
            }
        });
    }

    public function down(): void
    {
        Schema::table('video_templates', function (Blueprint $table) {
            foreach (['auto_subtitle', 'subtitle_position', 'subtitle_color'] as $column) {
                if (Schema::hasColumn('video_templates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('video_subtitles');
        Schema::dropIfExists('ai_contents');
    }
};
