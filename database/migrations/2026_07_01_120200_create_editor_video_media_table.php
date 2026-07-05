<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('editor_video_media')) {
            Schema::create('editor_video_media', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('editor_video_projects')->cascadeOnDelete();
                $table->string('original_name');
                $table->string('file_name');
                $table->string('path');
                $table->string('mime_type')->nullable();
                $table->string('media_type')->default('file');
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->decimal('duration_seconds', 10, 2)->nullable();
                $table->integer('width')->nullable();
                $table->integer('height')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('editor_video_media');
    }
};
