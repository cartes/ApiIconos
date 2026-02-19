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
        Schema::create('carpetas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre');
            // Relacionamos con la empresa usando UUID
            $table->uuid('empresaId');
            $table->string('creadoPor');
            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->foreign('empresaId')->references('id')->on('empresas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carpetas');
    }
};
