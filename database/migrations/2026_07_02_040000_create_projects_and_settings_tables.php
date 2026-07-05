<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('status')->default('ativo');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'key']);
        });

        Schema::table('videos', function (Blueprint $table) {
            if (!Schema::hasColumn('videos', 'video_project_id')) {
                $table->foreignId('video_project_id')->nullable()->after('video_folder_id')->constrained('video_projects')->nullOnDelete();
            }
        });

        Schema::table('video_exports', function (Blueprint $table) {
            if (!Schema::hasColumn('video_exports', 'video_project_id')) {
                $table->foreignId('video_project_id')->nullable()->after('video_template_id')->constrained('video_projects')->nullOnDelete();
            }

            if (!Schema::hasColumn('video_exports', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable();
            }

            if (!Schema::hasColumn('video_exports', 'priority')) {
                $table->integer('priority')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('video_exports', function (Blueprint $table) {
            if (Schema::hasColumn('video_exports', 'video_project_id')) {
                $table->dropConstrainedForeignId('video_project_id');
            }
            if (Schema::hasColumn('video_exports', 'scheduled_at')) {
                $table->dropColumn('scheduled_at');
            }
            if (Schema::hasColumn('video_exports', 'priority')) {
                $table->dropColumn('priority');
            }
        });

        Schema::table('videos', function (Blueprint $table) {
            if (Schema::hasColumn('videos', 'video_project_id')) {
                $table->dropConstrainedForeignId('video_project_id');
            }
        });

        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('video_projects');
    }
};
