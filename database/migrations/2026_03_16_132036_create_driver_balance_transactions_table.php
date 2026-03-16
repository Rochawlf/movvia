<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_balance_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('driver_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('ride_id')
                ->nullable()
                ->constrained('rides')
                ->nullOnDelete();

            $table->string('type', 50);
            // ride_cash_commission_due
            // ride_driver_credit
            // ride_platform_fee
            // debt_payment
            // withdrawal
            // adjustment

            $table->string('direction', 10);
            // credit | debit

            $table->decimal('amount', 10, 2);

            $table->string('status', 30)->default('posted');
            // pending | posted | cancelled

            $table->text('description')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index('driver_id');
            $table->index('ride_id');
            $table->index('type');
            $table->index('direction');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_balance_transactions');
    }
};