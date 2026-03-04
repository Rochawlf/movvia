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
        Schema::table('users', function (Blueprint $table){
            //Papel de Usuario no Sistema
            $table->string('role')->default('passenger')->after('password');

            // Status específico para motoristas
            $table->string('driver_status')->default('offline')->after('role'); 
            
            // Localização em tempo real (essencial para o match)
            $table->decimal('last_lat', 10, 8)->nullable()->after('driver_status');
            $table->decimal('last_lng', 11, 8)->nullable()->after('last_lat');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
