<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('editor_video_clips')) {
            Schema::create('editor_video_clips', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('editor_video_projects')->cascadeOnDelete();
                $table->foreignId('media_id')->nullable()->constrained('editor_video_media')->nullOnDelete();
                $table->string('name')->default('Clipe');
                $table->string('track')->default('video_1');
                $table->decimal('start_time', 10, 2)->default(0);
                $table->decimal('duration', 10, 2)->default(5);
                $table->decimal('end_time', 10, 2)->default(5);
                $table->integer('position')->default(0);
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('editor_video_clips');
    }
};
