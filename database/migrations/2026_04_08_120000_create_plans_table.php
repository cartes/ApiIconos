<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('storage_limit_gb')->default(0);
            $table->string('mp_plan_id')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('plans')->insert([
            'name' => 'Plan Medio',
            'description' => 'Compatibilidad heredada para Tenant 1: publica iconos mediante URLs externas y no consume almacenamiento fisico.',
            'price' => 0,
            'storage_limit_gb' => 0,
            'mp_plan_id' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
