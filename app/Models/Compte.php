<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    ];

    protected $casts = [
        'metadata' => 'array',
        'solde' => 'float',
        'deleted_at' => 'datetime',
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
            // Avant la suppression, changer le statut à "ferme" et définir deleted_at
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
}

