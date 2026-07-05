<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('media_assets')) {
            Schema::create('media_assets', function (Blueprint $table) {
                $table->id();
                $table->string('original_name');
                $table->string('stored_name');
                $table->string('mime_type')->nullable();
                $table->string('media_type', 30)->default('other');
                $table->string('extension', 20)->nullable();
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->decimal('duration_seconds', 10, 2)->nullable();
                $table->unsignedInteger('width')->nullable();
                $table->unsignedInteger('height')->nullable();
                $table->string('storage_path');
                $table->string('public_url');
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
