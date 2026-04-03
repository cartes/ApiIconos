<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Crear tu agencia actual como el primer Tenant en la tabla 'tenants'
        DB::table('tenants')->insert([
            'id' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Añadir la columna y actualizar los datos para la tabla USERS
        Schema::table('users', function (Blueprint $table) {
            $table->string('tenant_id')->nullable();
        });
        DB::table('users')->update(['tenant_id' => '1']); // Asigna tus usuarios actuales al tenant 1

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        // 3. Añadir la columna y actualizar los datos para CARPETAS
        Schema::table('carpetas', function (Blueprint $table) {
            $table->string('tenant_id')->nullable();
        });
        DB::table('carpetas')->update(['tenant_id' => '1']); // Asigna tus carpetas al tenant 1

        Schema::table('carpetas', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        // 4. Añadir la columna y actualizar los datos para ICONOS
        Schema::table('iconos', function (Blueprint $table) {
            $table->string('tenant_id')->nullable();
        });
        DB::table('iconos')->update(['tenant_id' => '1']); // Asigna tus iconos al tenant 1

        Schema::table('iconos', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('carpetas', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('iconos', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        // Eliminar el tenant insertado
        DB::table('tenants')->where('id', '1')->delete();
    }
};
