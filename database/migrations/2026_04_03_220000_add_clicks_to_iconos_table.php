<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('iconos', function (Blueprint $table) {
            $table->unsignedBigInteger('clicks')->default(0)->after('orden');
        });
    }

    public function down(): void
    {
        Schema::table('iconos', function (Blueprint $table) {
            $table->dropColumn('clicks');
        });
    }
};
