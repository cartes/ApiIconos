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
        // Renombramos la columna en la tabla de roles
        Schema::table('roles', function (Blueprint $table) {
            $table->renameColumn('team_id', 'tenant_id');
        });

        // Renombramos la columna en la tabla de asignación de roles
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->renameColumn('team_id', 'tenant_id');
        });

        // Renombramos la columna en la tabla de asignación de permisos
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->renameColumn('team_id', 'tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->renameColumn('tenant_id', 'team_id');
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->renameColumn('tenant_id', 'team_id');
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->renameColumn('tenant_id', 'team_id');
        });
    }
};
