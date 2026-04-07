<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email');
            $table->string('rol')->default('usuario');
            $table->string('token', 64)->unique();
            $table->string('tenant_id');
            $table->string('invited_by')->nullable();
            $table->enum('status', ['pending', 'accepted', 'expired'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['email', 'status']);
            $table->index(['token', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
