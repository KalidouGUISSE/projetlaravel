<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\Authenticatable;

class Client extends Model implements Authenticatable
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

    protected $casts = [
        'password' => 'hashed',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    // Implémentation de l'interface Authenticatable
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getRememberToken()
    {
        return $this->remember_token;
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    // Relations
    public function comptes()
    {
        return $this->hasMany(Compte::class);
    }
}