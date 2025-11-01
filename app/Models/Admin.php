<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

class Admin extends Model
{
    use HasFactory, HasApiTokens;

    protected $keyType = 'string';
    public $incrementing = false; // uuid

    protected $fillable = [
        'id', 'nom', 'prenom', 'email', 'telephone', 'password'
    ];

    protected $hidden = [
        'password',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }
}
