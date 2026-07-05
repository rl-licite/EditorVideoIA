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

            return;
        }

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
            DB::table('editor_video_projects')->whereNull('name')->update([
                'name' => DB::raw('title')
            ]);
        }
    }

    public function down(): void
    {
        //
    }
};