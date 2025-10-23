<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Compte;
use Illuminate\Support\Facades\Auth;

class CompteController extends Controller
{
    /**
    * Lister les comptes selon le rôle de l'utilisateur
    */
    public function index(Request $request)
    {
        // $user = Auth::user();

        // if (!$user) {
        //     return response()->json([
        //         'message' => 'Utilisateur non authentifié.'
        //     ], 401);
        // }

        // // Vérifier le rôle
        // if ($user->role === 'admin') {
        //     $comptes = Compte::with('client')->get();
        // } else {
        //     $comptes = Compte::where('client_id', $user->id)->get();
        // }

        // Récupérer tous les comptes avec leurs clients
        $comptes = Compte::with('client')->get();


        return response()->json([
            'status' => 'success',
            'data' => $comptes
        ]);
    }
}
