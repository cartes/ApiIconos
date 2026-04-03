<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icon_clicks', function (Blueprint $table) {
            $table->id();
            $table->string('user_email');
            $table->uuid('icono_id');
            $table->string('tenant_id')->nullable();
            $table->timestamps();

            $table->index(['user_email', 'tenant_id']);
            $table->index(['icono_id', 'tenant_id']);
            $table->foreign('icono_id')->references('id')->on('iconos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icon_clicks');
    }
};
