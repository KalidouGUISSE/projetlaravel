<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false; // uuid

    protected $fillable = [
        'id', 'nom', 'prenom', 'email', 'telephone'
    ];
}
