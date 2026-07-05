<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('monthly_credits')->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('max_videos')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('entrada');
            $table->integer('amount')->default(0);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'credits_balance')) {
                $table->integer('credits_balance')->default(100);
            }
            if (!Schema::hasColumn('users', 'subscription_plan_id')) {
                $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'subscription_plan_id')) {
                $table->dropConstrainedForeignId('subscription_plan_id');
            }
            if (Schema::hasColumn('users', 'credits_balance')) {
                $table->dropColumn('credits_balance');
            }
            if (Schema::hasColumn('users', 'is_admin')) {
                $table->dropColumn('is_admin');
            }
        });

        Schema::dropIfExists('credit_transactions');
        Schema::dropIfExists('subscription_plans');
    }
};
