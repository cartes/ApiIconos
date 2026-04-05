<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');                       // FK → tenants.id (UUID string)
            $table->foreignId('plan_id')->constrained('planes')->onDelete('cascade');
            $table->enum('estado', ['activa', 'vencida', 'cancelada', 'trial'])->default('trial');
            $table->date('fecha_inicio');
            $table->date('fecha_vencimiento')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            // Un tenant solo puede tener una suscripción activa a la vez
            $table->unique('tenant_id');

            // FK hacia tenants (string porque stancl/tenancy usa UUIDs o custom IDs)
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripciones');
    }
};
