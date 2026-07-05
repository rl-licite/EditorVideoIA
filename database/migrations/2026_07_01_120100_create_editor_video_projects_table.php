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
                $table->string('title')->default('Projeto sem nome');
                $table->text('description')->nullable();
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('editor_video_projects');
    }
};
