<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_templates');
    }
};
