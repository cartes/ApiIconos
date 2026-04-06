<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Eliminar tokens de los tenants
        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\Models\Tenant')
            ->delete();

        // 2. Eliminar columna allowed_domains de la tabla Sanctum
        if (Schema::hasColumn('personal_access_tokens', 'allowed_domains')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->dropColumn('allowed_domains');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->json('allowed_domains')->nullable()->after('abilities');
        });
    }
};
