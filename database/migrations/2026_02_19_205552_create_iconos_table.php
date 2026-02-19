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
        Schema::create('iconos', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Coincide con tu "id" (UUID)
            $table->string('url');
            $table->uuid('carpetaId')->nullable(); // Coincide con "carpetaId"
            $table->uuid('empresaId'); // Coincide con "empresaId"
            $table->string('subidoPor'); // Coincide con "subidoPor" (email)
            $table->dateTime('fechaSubida'); // Coincide con "fechaSubida" (ISO 8601)
            $table->string('etiqueta')->nullable();

            $table->integer('orden')->default(0); // Tu nueva mejora para el orden
            $table->timestamps(); // Registros de Laravel (created_at, updated_at)

            // Definimos las llaves foráneas para mantener la integridad
            $table->foreign('carpetaId')->references('id')->on('carpetas')->onDelete('set null');
            $table->foreign('empresaId')->references('id')->on('empresas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iconos');
    }
};
