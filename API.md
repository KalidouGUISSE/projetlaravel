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