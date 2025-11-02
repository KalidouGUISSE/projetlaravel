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
        Schema::table('operation_logs', function (Blueprint $table) {
            // Supprimer la contrainte étrangère de operation_logs
            $table->dropForeign(['user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            // Changer le type de la colonne id
            $table->string('id', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->id()->change();
        });

        Schema::table('operation_logs', function (Blueprint $table) {
            // Recréer la contrainte étrangère
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};
