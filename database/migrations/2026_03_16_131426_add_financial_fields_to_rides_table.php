<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            if (!Schema::hasColumn('rides', 'payment_method')) {
                $table->string('payment_method', 30)->nullable()->after('category');
            }

            if (!Schema::hasColumn('rides', 'payment_status')) {
                $table->string('payment_status', 30)->default('pending')->after('payment_method');
            }

            if (!Schema::hasColumn('rides', 'fare_total')) {
                $table->decimal('fare_total', 10, 2)->nullable()->after('fare');
            }

            if (!Schema::hasColumn('rides', 'platform_fee')) {
                $table->decimal('platform_fee', 10, 2)->default(0)->after('fare_total');
            }

            if (!Schema::hasColumn('rides', 'gateway_fee')) {
                $table->decimal('gateway_fee', 10, 2)->default(0)->after('platform_fee');
            }

            if (!Schema::hasColumn('rides', 'driver_net_amount')) {
                $table->decimal('driver_net_amount', 10, 2)->default(0)->after('gateway_fee');
            }

            if (!Schema::hasColumn('rides', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('driver_net_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            if (Schema::hasColumn('rides', 'completed_at')) {
                $table->dropColumn('completed_at');
            }

            if (Schema::hasColumn('rides', 'driver_net_amount')) {
                $table->dropColumn('driver_net_amount');
            }

            if (Schema::hasColumn('rides', 'gateway_fee')) {
                $table->dropColumn('gateway_fee');
            }

            if (Schema::hasColumn('rides', 'platform_fee')) {
                $table->dropColumn('platform_fee');
            }

            if (Schema::hasColumn('rides', 'fare_total')) {
                $table->dropColumn('fare_total');
            }

            if (Schema::hasColumn('rides', 'payment_status')) {
                $table->dropColumn('payment_status');
            }

            if (Schema::hasColumn('rides', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};