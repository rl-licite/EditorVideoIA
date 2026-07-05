<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            });

            if (Schema::hasColumn('editor_video_projects', 'title')) {
                try {
                    DB::table('editor_video_projects')
                        ->where(function ($query) {
                            $query->whereNull('name')->orWhere('name', '');
                        })
                        ->update(['name' => DB::raw('title')]);
                } catch (\Throwable $e) {
                    // SQLite pode variar conforme estrutura antiga. Mantem seguro.
                }
            }
        }

        if (!Schema::hasTable('media_assets')) {
            Schema::create('media_assets', function (Blueprint $table) {
                $table->id();
                $table->string('original_name');
                $table->string('stored_name')->nullable();
                $table->string('mime_type')->nullable();
                $table->string('media_type')->default('other');
                $table->string('extension', 20)->nullable();
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->decimal('duration_seconds', 10, 2)->nullable();
                $table->integer('width')->nullable();
                $table->integer('height')->nullable();
                $table->string('storage_path')->nullable();
                $table->string('public_url')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('media_assets', function (Blueprint $table) {
                if (!Schema::hasColumn('media_assets', 'original_name')) $table->string('original_name')->nullable();
                if (!Schema::hasColumn('media_assets', 'stored_name')) $table->string('stored_name')->nullable();
                if (!Schema::hasColumn('media_assets', 'mime_type')) $table->string('mime_type')->nullable();
                if (!Schema::hasColumn('media_assets', 'media_type')) $table->string('media_type')->default('other');
                if (!Schema::hasColumn('media_assets', 'extension')) $table->string('extension', 20)->nullable();
                if (!Schema::hasColumn('media_assets', 'size_bytes')) $table->unsignedBigInteger('size_bytes')->default(0);
                if (!Schema::hasColumn('media_assets', 'duration_seconds')) $table->decimal('duration_seconds', 10, 2)->nullable();
                if (!Schema::hasColumn('media_assets', 'width')) $table->integer('width')->nullable();
                if (!Schema::hasColumn('media_assets', 'height')) $table->integer('height')->nullable();
                if (!Schema::hasColumn('media_assets', 'storage_path')) $table->string('storage_path')->nullable();
                if (!Schema::hasColumn('media_assets', 'public_url')) $table->string('public_url')->nullable();
                if (!Schema::hasColumn('media_assets', 'metadata')) $table->json('metadata')->nullable();
            });
        }
    }

    public function down(): void
    {
        //
    }
};
