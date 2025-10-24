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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('titulaire')->nullable()->after('prenom');
            $table->string('password')->nullable()->after('titulaire');
            $table->string('code')->nullable()->after('password');
            $table->string('nci')->nullable()->after('code');
            $table->string('adresse')->nullable()->after('nci');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['nci', 'adresse']);
        });
    }
};