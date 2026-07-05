<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            if (!Schema::hasColumn('videos', 'thumbnail_path')) {
                $table->string('thumbnail_path')->nullable()->after('path');
            }

            if (!Schema::hasColumn('videos', 'width')) {
                $table->integer('width')->nullable()->after('duration');
            }

            if (!Schema::hasColumn('videos', 'height')) {
                $table->integer('height')->nullable()->after('width');
            }

            if (!Schema::hasColumn('videos', 'format')) {
                $table->string('format')->nullable()->after('height');
            }

            if (!Schema::hasColumn('videos', 'codec')) {
                $table->string('codec')->nullable()->after('format');
            }

            if (!Schema::hasColumn('videos', 'fps')) {
                $table->decimal('fps', 8, 2)->nullable()->after('codec');
            }
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $columns = ['thumbnail_path', 'width', 'height', 'format', 'codec', 'fps'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('videos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
