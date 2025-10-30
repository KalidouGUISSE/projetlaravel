<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('operation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('operation'); // GET, POST, PUT, DELETE
            $table->string('resource'); // URI de la ressource
            $table->integer('status_code');
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->json('request_data')->nullable(); // Données de la requête
            $table->timestamps();

            // Index pour les performances
            $table->index(['user_id', 'created_at']);
            $table->index(['operation', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_logs');
    }
};
