<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix Spatie permission tables to support UUID foreign keys.
 *
 * The default Spatie migration uses `unsignedBigInteger` for:
 *   - `tenant_id` (team_foreign_key) in `roles`, `model_has_roles`, `model_has_permissions`
 *   - `model_id` (model_morph_key) in `model_has_roles`, `model_has_permissions`
 *
 * This project uses UUID primary keys for both Tenant and User models,
 * so those columns must be `varchar(255)` instead.
 *
 * Altering a primary key column in PostgreSQL requires:
 *   1. Drop the constraint/index
 *   2. Alter the column type
 *   3. Re-create the constraint/index
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── roles: change tenant_id bigint → string ────────────────────────
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropIndex('roles_team_foreign_key_index');
            $table->dropUnique(['tenant_id', 'name', 'guard_name']);
        });

        // PostgreSQL requires an explicit USING clause when casting bigint → varchar.
        \DB::statement('ALTER TABLE roles ALTER COLUMN tenant_id TYPE varchar(255) USING tenant_id::varchar');

        Schema::table('roles', function (Blueprint $table): void {
            $table->index('tenant_id', 'roles_team_foreign_key_index');
            $table->unique(['tenant_id', 'name', 'guard_name']);
        });

        // ── model_has_roles: change tenant_id and model_id ────────────────
        Schema::table('model_has_roles', function (Blueprint $table): void {
            $table->dropPrimary('model_has_roles_role_model_type_primary');
            $table->dropIndex('model_has_roles_team_foreign_key_index');
            $table->dropIndex('model_has_roles_model_id_model_type_index');
        });

        \DB::statement('ALTER TABLE model_has_roles ALTER COLUMN tenant_id TYPE varchar(255) USING tenant_id::varchar');
        \DB::statement('ALTER TABLE model_has_roles ALTER COLUMN model_id  TYPE varchar(255) USING model_id::varchar');

        Schema::table('model_has_roles', function (Blueprint $table): void {
            $table->index('tenant_id', 'model_has_roles_team_foreign_key_index');
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->primary(
                ['tenant_id', 'role_id', 'model_id', 'model_type'],
                'model_has_roles_role_model_type_primary'
            );
        });

        // ── model_has_permissions: change tenant_id and model_id ──────────
        Schema::table('model_has_permissions', function (Blueprint $table): void {
            $table->dropPrimary('model_has_permissions_permission_model_type_primary');
            $table->dropIndex('model_has_permissions_team_foreign_key_index');
            $table->dropIndex('model_has_permissions_model_id_model_type_index');
        });

        \DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN tenant_id TYPE varchar(255) USING tenant_id::varchar');
        \DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN model_id  TYPE varchar(255) USING model_id::varchar');

        Schema::table('model_has_permissions', function (Blueprint $table): void {
            $table->index('tenant_id', 'model_has_permissions_team_foreign_key_index');
            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->primary(
                ['tenant_id', 'permission_id', 'model_id', 'model_type'],
                'model_has_permissions_permission_model_type_primary'
            );
        });
    }

    public function down(): void
    {
        // Revert model_has_permissions
        Schema::table('model_has_permissions', function (Blueprint $table): void {
            $table->dropPrimary('model_has_permissions_permission_model_type_primary');
            $table->dropIndex('model_has_permissions_team_foreign_key_index');
            $table->dropIndex('model_has_permissions_model_id_model_type_index');
        });

        \DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN tenant_id TYPE bigint USING tenant_id::bigint');
        \DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN model_id  TYPE bigint USING model_id::bigint');

        Schema::table('model_has_permissions', function (Blueprint $table): void {
            $table->index('tenant_id', 'model_has_permissions_team_foreign_key_index');
            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->primary(
                ['tenant_id', 'permission_id', 'model_id', 'model_type'],
                'model_has_permissions_permission_model_type_primary'
            );
        });

        // Revert model_has_roles
        Schema::table('model_has_roles', function (Blueprint $table): void {
            $table->dropPrimary('model_has_roles_role_model_type_primary');
            $table->dropIndex('model_has_roles_team_foreign_key_index');
            $table->dropIndex('model_has_roles_model_id_model_type_index');
        });

        \DB::statement('ALTER TABLE model_has_roles ALTER COLUMN tenant_id TYPE bigint USING tenant_id::bigint');
        \DB::statement('ALTER TABLE model_has_roles ALTER COLUMN model_id  TYPE bigint USING model_id::bigint');

        Schema::table('model_has_roles', function (Blueprint $table): void {
            $table->index('tenant_id', 'model_has_roles_team_foreign_key_index');
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->primary(
                ['tenant_id', 'role_id', 'model_id', 'model_type'],
                'model_has_roles_role_model_type_primary'
            );
        });

        // Revert roles
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropIndex('roles_team_foreign_key_index');
            $table->dropUnique(['tenant_id', 'name', 'guard_name']);
        });

        \DB::statement('ALTER TABLE roles ALTER COLUMN tenant_id TYPE bigint USING tenant_id::bigint');

        Schema::table('roles', function (Blueprint $table): void {
            $table->index('tenant_id', 'roles_team_foreign_key_index');
            $table->unique(['tenant_id', 'name', 'guard_name']);
        });
    }
};
