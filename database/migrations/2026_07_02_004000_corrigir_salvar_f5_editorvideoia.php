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
                if (!Schema::hasColumn('editor_video_projects', 'name')) {
                    $table->string('name')->default('Projeto de teste');
                }
                if (!Schema::hasColumn('editor_video_projects', 'timeline_data')) {
                    $table->json('timeline_data')->nullable();
                }
                if (!Schema::hasColumn('editor_video_projects', 'duration_seconds')) {
                    $table->integer('duration_seconds')->default(0);
                }
                if (!Schema::hasColumn('editor_video_projects', 'settings')) {
                    $table->json('settings')->nullable();
                }
            });
        }

        if (!Schema::hasTable('media_assets')) {
            Schema::create('media_assets', function (Blueprint $table) {
                $table->id();
                $table->string('original_name');
                $table->string('stored_name')->nullable();
                $table->string('mime_type')->nullable();
                $table->string('media_type')->nullable();
                $table->string('extension')->nullable();
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->decimal('duration_seconds', 10, 2)->nullable();
                $table->integer('width')->nullable();
                $table->integer('height')->nullable();
                $table->string('storage_path')->nullable();
                $table->string('public_url')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Correção segura: não remove dados do usuário.
    }
};
