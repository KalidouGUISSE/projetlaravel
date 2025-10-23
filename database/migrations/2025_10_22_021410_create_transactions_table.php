<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('compte_id')->index(); // ✅ Jointure rapide
            $table->enum('type', ['depot', 'retrait', 'virement', 'frais'])->index();
            $table->decimal('montant', 15, 2);
            $table->string('devise', 3)->default('XOF')->index();
            $table->string('description')->nullable();
            $table->dateTime('dateTransaction')->index();
            $table->enum('statut', ['en_attente', 'validee', 'annulee'])->default('en_attente')->index();
            $table->timestamps();

            $table->foreign('compte_id')->references('id')->on('comptes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
