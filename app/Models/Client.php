<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// class Client extends Model
// {
//     use HasFactory;
// }


class Client extends Model
{
    use HasFactory;

    
    protected $keyType = 'string';
    public $incrementing = false; // uuid
    
    // protected $fillable = [
    //     'nom',
    //     'prenom',
    //     'email',
    //     'telephone',
    //     'adresse',
    //     // ajoute d’autres colonnes selon ta migration
    // ];
    protected $fillable = [
        'id', 'nom', 'prenom', 'email', 'telephone'
    ];

    // Relations
    public function comptes()
    {
        return $this->hasMany(Compte::class);
    }
}