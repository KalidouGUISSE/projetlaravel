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
        Schema::connection('archive')->create('comptes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numeroCompte')->unique();
            $table->uuid('client_id')->index(); // ✅ Index pour les jointures
            $table->enum('type', ['epargne', 'cheque'])->index();
            $table->decimal('solde', 15, 2)->default(0);
            $table->enum('statut', ['actif', 'bloque', 'ferme'])->default('actif')->index();
            $table->json('metadata')->nullable();
            $table->string('motifBlocage')->nullable();
            $table->timestamp('date_debut_blocage')->nullable();
            $table->timestamp('date_fin_blocage')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
