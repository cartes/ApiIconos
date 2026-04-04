<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');                          // "Starter", "Pro", "Enterprise"
            $table->decimal('precio_mensual', 10, 2)->default(0);
            $table->unsignedInteger('max_usuarios')->nullable(); // null = ilimitado
            $table->unsignedInteger('max_iconos')->nullable();   // null = ilimitado
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
