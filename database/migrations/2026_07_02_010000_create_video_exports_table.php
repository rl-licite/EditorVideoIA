<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('video_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('video_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pendente');
            $table->integer('progress')->default(0);
            $table->string('output_path')->nullable();
            $table->text('ffmpeg_command')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_exports');
    }
};
