# API Documentation

## Introduction

Cette API permet de gérer les clients, comptes, admins et transactions pour une application bancaire.

## Base URL

`http://localhost:8001/api/v1`

## Authentication

Utiliser Sanctum pour l'authentification.

## Routes

### Clients

- **GET /clients** : Lister tous les clients
- **POST /clients** : Créer un nouveau client
- **GET /clients/{id}** : Afficher un client spécifique
- **PUT /clients/{id}** : Mettre à jour un client
- **DELETE /clients/{id}** : Supprimer un client

### Comptes

- **GET /comptes** : Lister les comptes d'un client (avec client_id)
- **POST /comptes** : Créer un nouveau compte (avec création de client si nécessaire)

#### Exemple - Créer un compte
```bash
curl -X POST http://localhost:8001/api/v1/comptes \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "cheque",
    "soldeInitial": 500000,
    "devise": "FCFA",
    "client": {
      "id": null,
      "titulaire": "Hawa BB Wane",
      "nci": "1234567890123",
      "email": "cheikh.sy@example.com",
      "telephone": "+221771234567",
      "adresse": "Dakar, Sénégal"
    }
  }'
```

**Request Body :**
- `type` (string, required) : Type de compte (cheque, epargne)
- `soldeInitial` (number, required) : Solde initial (>= 10000)
- `devise` (string, required) : Devise (FCFA)
- `client` (object, required) :
  - `id` (uuid, optional) : ID du client existant
  - `titulaire` (string, required) : Nom du titulaire
  - `nci` (string, optional) : Numéro de Carte d'Identité (13 chiffres)
  - `email` (string, required) : Email unique
  - `telephone` (string, required) : Téléphone portable Sénégalais (+221...)
  - `adresse` (string, required) : Adresse

**Response (201) :**
```json
{
  "success": true,
  "message": "Compte créé avec succès",
  "data": {
    "id": "uuid",
    "numeroCompte": "C00123460",
    "titulaire": "Hawa BB Wane",
    "type": "cheque",
    "solde": 500000,
    "devise": "FCFA",
    "dateCreation": "2025-10-19T10:30:00Z",
    "statut": "actif",
    "metadata": {
      "derniereModification": "2025-10-19T10:30:00Z",
      "version": 1
    }
  }
}
```

### Admins

- **POST /admins** : Créer un nouvel admin
- **GET /admins/comptes** : Lister tous les comptes (admin)

### Autres

- **GET /request** : Tester la requête

## Responses

Toutes les réponses sont en JSON avec le format :

```json
{
  "success": true,
  "message": "Message",
  "data": {}
}
```

## Errors

- 404 : Not Found
- 401 : Unauthorized
- 422 : Validation Error