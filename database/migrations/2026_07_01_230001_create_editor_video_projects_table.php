<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('editor_video_projects')) {
            Schema::create('editor_video_projects', function (Blueprint $table) {
                $table->id();
                $table->string('name')->default('Projeto de teste');
                $table->json('timeline_data')->nullable();
                $table->decimal('duration_seconds', 10, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('editor_video_projects');
    }
};
