<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            // 1. Adiciona a Categoria (se não existir)
            if (!Schema::hasColumn('rides', 'category')) {
                $table->string('category')->default('car')->after('status');
            }

            // 2. Adiciona as Coordenadas de Origem
            $table->decimal('origin_lat', 10, 8)->nullable()->after('origin_address');
            $table->decimal('origin_lng', 11, 8)->nullable()->after('origin_lat');

            // 3. Adiciona as Coordenadas de Destino
            $table->decimal('destination_lat', 10, 8)->nullable()->after('destination_address');
            $table->decimal('destination_lng', 11, 8)->nullable()->after('destination_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            // Remove tudo o que criamos no 'up' caso precise desfazer
            $table->dropColumn([
                'category',
                'origin_lat',
                'origin_lng',
                'destination_lat',
                'destination_lng'
            ]);
        });
    }
};
