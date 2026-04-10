<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
 * This migration is idempotent: it checks if indexes/constraints exist
 * before attempting to drop or create them, preventing failures on re-runs
 * or when the DB state differs from what is expected.
 */
return new class extends Migration
{
    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /** Check if an index exists in PostgreSQL */
    private function indexExists(string $indexName): bool
    {
        return (bool) DB::selectOne(
            "SELECT 1 FROM pg_indexes WHERE indexname = ?",
            [$indexName]
        );
    }

    /** Check if a constraint (primary key, unique, FK) exists in PostgreSQL */
    private function constraintExists(string $constraintName): bool
    {
        return (bool) DB::selectOne(
            "SELECT 1 FROM pg_constraint WHERE conname = ?",
            [$constraintName]
        );
    }

    /** Check if a column already has a given data type in PostgreSQL */
    private function columnIsType(string $table, string $column, string $type): bool
    {
        $result = DB::selectOne(
            "SELECT data_type FROM information_schema.columns
             WHERE table_name = ? AND column_name = ?",
            [$table, $column]
        );

        return $result && str_contains(strtolower($result->data_type), $type);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // UP
    // ──────────────────────────────────────────────────────────────────────────

    public function up(): void
    {
        // ── roles: tenant_id bigint → varchar ─────────────────────────────────
        if ($this->indexExists('roles_team_foreign_key_index')) {
            DB::statement('DROP INDEX IF EXISTS roles_team_foreign_key_index');
        }

        // The unique constraint name that Spatie creates:
        // roles_tenant_id_name_guard_name_unique
        if ($this->constraintExists('roles_tenant_id_name_guard_name_unique')) {
            DB::statement('ALTER TABLE roles DROP CONSTRAINT IF EXISTS roles_tenant_id_name_guard_name_unique');
        }

        if (! $this->columnIsType('roles', 'tenant_id', 'character')) {
            DB::statement('ALTER TABLE roles ALTER COLUMN tenant_id TYPE varchar(255) USING tenant_id::varchar');
        }

        if (! $this->indexExists('roles_team_foreign_key_index')) {
            DB::statement('CREATE INDEX roles_team_foreign_key_index ON roles (tenant_id)');
        }

        if (! $this->constraintExists('roles_tenant_id_name_guard_name_unique')) {
            DB::statement('ALTER TABLE roles ADD CONSTRAINT roles_tenant_id_name_guard_name_unique UNIQUE (tenant_id, name, guard_name)');
        }

        // ── model_has_roles: tenant_id and model_id bigint → varchar ──────────
        if ($this->constraintExists('model_has_roles_role_model_type_primary')) {
            DB::statement('ALTER TABLE model_has_roles DROP CONSTRAINT IF EXISTS model_has_roles_role_model_type_primary');
        }

        if ($this->indexExists('model_has_roles_team_foreign_key_index')) {
            DB::statement('DROP INDEX IF EXISTS model_has_roles_team_foreign_key_index');
        }

        if ($this->indexExists('model_has_roles_model_id_model_type_index')) {
            DB::statement('DROP INDEX IF EXISTS model_has_roles_model_id_model_type_index');
        }

        if (! $this->columnIsType('model_has_roles', 'tenant_id', 'character')) {
            DB::statement('ALTER TABLE model_has_roles ALTER COLUMN tenant_id TYPE varchar(255) USING tenant_id::varchar');
        }

        if (! $this->columnIsType('model_has_roles', 'model_id', 'character')) {
            DB::statement('ALTER TABLE model_has_roles ALTER COLUMN model_id TYPE varchar(255) USING model_id::varchar');
        }

        if (! $this->indexExists('model_has_roles_team_foreign_key_index')) {
            DB::statement('CREATE INDEX model_has_roles_team_foreign_key_index ON model_has_roles (tenant_id)');
        }

        if (! $this->indexExists('model_has_roles_model_id_model_type_index')) {
            DB::statement('CREATE INDEX model_has_roles_model_id_model_type_index ON model_has_roles (model_id, model_type)');
        }

        if (! $this->constraintExists('model_has_roles_role_model_type_primary')) {
            DB::statement('ALTER TABLE model_has_roles ADD CONSTRAINT model_has_roles_role_model_type_primary PRIMARY KEY (tenant_id, role_id, model_id, model_type)');
        }

        // ── model_has_permissions: tenant_id and model_id bigint → varchar ─────
        if ($this->constraintExists('model_has_permissions_permission_model_type_primary')) {
            DB::statement('ALTER TABLE model_has_permissions DROP CONSTRAINT IF EXISTS model_has_permissions_permission_model_type_primary');
        }

        if ($this->indexExists('model_has_permissions_team_foreign_key_index')) {
            DB::statement('DROP INDEX IF EXISTS model_has_permissions_team_foreign_key_index');
        }

        if ($this->indexExists('model_has_permissions_model_id_model_type_index')) {
            DB::statement('DROP INDEX IF EXISTS model_has_permissions_model_id_model_type_index');
        }

        if (! $this->columnIsType('model_has_permissions', 'tenant_id', 'character')) {
            DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN tenant_id TYPE varchar(255) USING tenant_id::varchar');
        }

        if (! $this->columnIsType('model_has_permissions', 'model_id', 'character')) {
            DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN model_id TYPE varchar(255) USING model_id::varchar');
        }

        if (! $this->indexExists('model_has_permissions_team_foreign_key_index')) {
            DB::statement('CREATE INDEX model_has_permissions_team_foreign_key_index ON model_has_permissions (tenant_id)');
        }

        if (! $this->indexExists('model_has_permissions_model_id_model_type_index')) {
            DB::statement('CREATE INDEX model_has_permissions_model_id_model_type_index ON model_has_permissions (model_id, model_type)');
        }

        if (! $this->constraintExists('model_has_permissions_permission_model_type_primary')) {
            DB::statement('ALTER TABLE model_has_permissions ADD CONSTRAINT model_has_permissions_permission_model_type_primary PRIMARY KEY (tenant_id, permission_id, model_id, model_type)');
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DOWN
    // ──────────────────────────────────────────────────────────────────────────

    public function down(): void
    {
        // ── model_has_permissions: varchar → bigint ────────────────────────────
        if ($this->constraintExists('model_has_permissions_permission_model_type_primary')) {
            DB::statement('ALTER TABLE model_has_permissions DROP CONSTRAINT IF EXISTS model_has_permissions_permission_model_type_primary');
        }

        if ($this->indexExists('model_has_permissions_team_foreign_key_index')) {
            DB::statement('DROP INDEX IF EXISTS model_has_permissions_team_foreign_key_index');
        }

        if ($this->indexExists('model_has_permissions_model_id_model_type_index')) {
            DB::statement('DROP INDEX IF EXISTS model_has_permissions_model_id_model_type_index');
        }

        DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN tenant_id TYPE bigint USING tenant_id::bigint');
        DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN model_id TYPE bigint USING model_id::bigint');

        DB::statement('CREATE INDEX model_has_permissions_team_foreign_key_index ON model_has_permissions (tenant_id)');
        DB::statement('CREATE INDEX model_has_permissions_model_id_model_type_index ON model_has_permissions (model_id, model_type)');
        DB::statement('ALTER TABLE model_has_permissions ADD CONSTRAINT model_has_permissions_permission_model_type_primary PRIMARY KEY (tenant_id, permission_id, model_id, model_type)');

        // ── model_has_roles: varchar → bigint ─────────────────────────────────
        if ($this->constraintExists('model_has_roles_role_model_type_primary')) {
            DB::statement('ALTER TABLE model_has_roles DROP CONSTRAINT IF EXISTS model_has_roles_role_model_type_primary');
        }

        if ($this->indexExists('model_has_roles_team_foreign_key_index')) {
            DB::statement('DROP INDEX IF EXISTS model_has_roles_team_foreign_key_index');
        }

        if ($this->indexExists('model_has_roles_model_id_model_type_index')) {
            DB::statement('DROP INDEX IF EXISTS model_has_roles_model_id_model_type_index');
        }

        DB::statement('ALTER TABLE model_has_roles ALTER COLUMN tenant_id TYPE bigint USING tenant_id::bigint');
        DB::statement('ALTER TABLE model_has_roles ALTER COLUMN model_id TYPE bigint USING model_id::bigint');

        DB::statement('CREATE INDEX model_has_roles_team_foreign_key_index ON model_has_roles (tenant_id)');
        DB::statement('CREATE INDEX model_has_roles_model_id_model_type_index ON model_has_roles (model_id, model_type)');
        DB::statement('ALTER TABLE model_has_roles ADD CONSTRAINT model_has_roles_role_model_type_primary PRIMARY KEY (tenant_id, role_id, model_id, model_type)');

        // ── roles: varchar → bigint ────────────────────────────────────────────
        if ($this->indexExists('roles_team_foreign_key_index')) {
            DB::statement('DROP INDEX IF EXISTS roles_team_foreign_key_index');
        }

        if ($this->constraintExists('roles_tenant_id_name_guard_name_unique')) {
            DB::statement('ALTER TABLE roles DROP CONSTRAINT IF EXISTS roles_tenant_id_name_guard_name_unique');
        }

        DB::statement('ALTER TABLE roles ALTER COLUMN tenant_id TYPE bigint USING tenant_id::bigint');

        DB::statement('CREATE INDEX roles_team_foreign_key_index ON roles (tenant_id)');
        DB::statement('ALTER TABLE roles ADD CONSTRAINT roles_tenant_id_name_guard_name_unique UNIQUE (tenant_id, name, guard_name)');
    }
};
