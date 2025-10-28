<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Compte extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    // protected $fillable = [
    //     'id', 'numeroCompte', 'client_id', 'type', 'solde', 'statut', 'metadata'
    // ];
    protected $fillable = [
        'client_id',
        'numeroCompte',
        'type',
        'solde',
        'statut',
        'metadata',
        'motifBlocage',
        'date_debut_blocage',
        'date_fin_blocage',
    ];

    protected $casts = [
        'metadata' => 'array',
        'solde' => 'float',
        'deleted_at' => 'datetime',
        'date_debut_blocage' => 'datetime',
        'date_fin_blocage' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'compte_id');
    }


    // Génération automatique du numéro de compte
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($compte) {
            // Générer l'UUID si non défini
            if (empty($compte->id)) {
                $compte->id = (string) Str::uuid();
            }

            // Générer un numéro de compte unique si non défini
            if (empty($compte->numeroCompte)) {
                $compte->numeroCompte = self::generateNumeroCompte();
            }

            // Metadata par défaut
            if (empty($compte->metadata)) {
                $compte->metadata = [
                    'derniereModification' => now(),
                    'version' => 1
                ];
            }
        });

        static::deleting(function ($compte) {
            // Avant la suppression, archiver vers Neon
            self::archiveToNeon($compte);

            // Changer le statut à "ferme" et définir deleted_at
            $compte->statut = 'ferme';
            $compte->deleted_at = now();
            $compte->save();
        });
    }

    // Fonction pour générer un numéro de compte unique
    protected static function generateNumeroCompte(): string
    {
        do {
            $numero = 'COMP-' . mt_rand(10000000, 99999999); // format : COMP-XXXXXXXX
        } while (self::where('numeroCompte', $numero)->exists());

        return $numero;
    }

    /**
     * Archive le compte vers la base Neon
     */
    protected static function archiveToNeon(Compte $compte): void
    {
        try {
            DB::connection('archive')->table('comptes')->insert([
                'id' => $compte->id,
                'numeroCompte' => $compte->numeroCompte,
                'client_id' => $compte->client_id,
                'type' => $compte->type,
                'solde' => $compte->solde,
                'statut' => $compte->statut,
                'metadata' => json_encode($compte->metadata),
                'motifBlocage' => $compte->motifBlocage,
                'date_debut_blocage' => $compte->date_debut_blocage,
                'date_fin_blocage' => $compte->date_fin_blocage,
                'created_at' => $compte->created_at,
                'updated_at' => $compte->updated_at,
                'deleted_at' => now(),
            ]);

            Log::info("Compte archivé vers Neon: {$compte->numeroCompte}");
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'archivage du compte {$compte->numeroCompte}: " . $e->getMessage());
            // Ne pas throw l'exception pour ne pas bloquer la suppression
        }
    }
}

