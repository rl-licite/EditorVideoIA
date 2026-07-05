<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->decimal('duration', 10, 2)->nullable();
            $table->string('status')->default('enviado');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('videos'); }
};
