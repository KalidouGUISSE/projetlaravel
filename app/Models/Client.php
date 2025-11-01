<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;

class Client extends Model
{
    use HasFactory, HasApiTokens;

    protected $keyType = 'string';
    public $incrementing = false; // uuid

    protected $fillable = [
        'id', 'nom', 'prenom', 'titulaire', 'email', 'telephone', 'nci', 'adresse', 'password', 'code'
    ];

    protected $hidden = [
        'password',
    ];

    // Relations
    public function comptes()
    {
        return $this->hasMany(Compte::class);
    }
}