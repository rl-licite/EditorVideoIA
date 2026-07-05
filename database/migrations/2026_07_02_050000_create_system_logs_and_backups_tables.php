<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('level')->default('info');
            $table->string('area')->nullable();
            $table->string('message');
            $table->json('context')->nullable();
            $table->timestamps();
        });

        Schema::create('backup_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('path')->nullable();
            $table->string('status')->default('criado');
            $table->unsignedBigInteger('size')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('video_exports', function (Blueprint $table) {
            if (!Schema::hasColumn('video_exports', 'retry_count')) {
                $table->integer('retry_count')->default(0);
            }

            if (!Schema::hasColumn('video_exports', 'render_seconds')) {
                $table->integer('render_seconds')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('video_exports', function (Blueprint $table) {
            if (Schema::hasColumn('video_exports', 'retry_count')) {
                $table->dropColumn('retry_count');
            }

            if (Schema::hasColumn('video_exports', 'render_seconds')) {
                $table->dropColumn('render_seconds');
            }
        });

        Schema::dropIfExists('backup_records');
        Schema::dropIfExists('system_logs');
    }
};
