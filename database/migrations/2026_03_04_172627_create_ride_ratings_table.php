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
        Schema::create('ride_ratings', function (Blueprint $table) {
            $table->foreignId('ride_id')->constrained();
            $table->foreignId('driver_id')->constrained('users');
            $table->integer('stars'); // 1 a 5
            $table->json('complaints')->nullable(); // Guardar as 5 opções clicadas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_ratings');
    }
};
