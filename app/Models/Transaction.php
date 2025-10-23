<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'compte_id', 'type', 'montant', 'devise', 'description', 'dateTransaction', 'statut'
    ];

    protected $casts = [
        'montant' => 'float',
        'dateTransaction' => 'datetime',
    ];

    public function compte()
    {
        return $this->belongsTo(Compte::class, 'compte_id');
    }
}
