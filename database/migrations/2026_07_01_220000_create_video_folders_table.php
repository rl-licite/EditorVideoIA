<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('color')->default('#111827');
            $table->timestamps();
        });

        Schema::table('videos', function (Blueprint $table) {
            if (!Schema::hasColumn('videos', 'video_folder_id')) {
                $table->foreignId('video_folder_id')->nullable()->after('user_id')->constrained('video_folders')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            if (Schema::hasColumn('videos', 'video_folder_id')) {
                $table->dropConstrainedForeignId('video_folder_id');
            }
        });

        Schema::dropIfExists('video_folders');
    }
};
