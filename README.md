# API de Gestion Bancaire Laravel

Une API RESTful développée avec Laravel pour la gestion de clients, comptes bancaires et transactions. Cette API inclut une documentation interactive Swagger et suit les meilleures pratiques de développement.

## 📋 Description

Cette API permet de :
- **Gérer les clients** : CRUD complet avec validation
- **Gérer les comptes bancaires** : Création et consultation avec génération automatique de numéros de compte
- **Suivre les transactions** : Historique des opérations bancaires
- **Documentation interactive** : Interface Swagger UI pour tester l'API

## 🚀 Fonctionnalités

- ✅ Authentification via Laravel Sanctum
- ✅ Validation des données avec des Request Classes personnalisées
- ✅ Règles de validation personnalisées pour téléphone et NCI Sénégalais
- ✅ Génération automatique d'UUID pour les entités
- ✅ Génération automatique de numéros de compte uniques
- ✅ Relations Eloquent entre Client, Compte et Transaction
- ✅ Documentation OpenAPI/Swagger complète
- ✅ Interface Swagger UI interactive
- ✅ Factories et Seeders pour les tests
- ✅ Migrations avec index optimisés
- ✅ Rate limiting et sécurité avancée
- ✅ Envoi d'email et SMS simulé pour authentification

## 📋 Prérequis

- PHP >= 8.1
- Composer
- MySQL ou PostgreSQL
- Node.js et npm (pour les assets frontend)

## ⚡ Installation

1. **Cloner le projet**
   ```bash
   git clone <url-du-repo>
   cd projetLaravel
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```

3. **Configurer l'environnement**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configurer la base de données**
   Modifiez le fichier `.env` avec vos paramètres de base de données :
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bank_api
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Exécuter les migrations**
   ```bash
   php artisan migrate
   ```

6. **Optionnel : Alimenter la base avec des données de test**
   ```bash
   php artisan db:seed
   ```

7. **Démarrer le serveur**
   ```bash
   php artisan serve
   ```

L'API sera accessible sur `http://localhost:8000`

## 🔧 Configuration

### Authentification
L'API utilise Laravel Sanctum pour l'authentification. Pour utiliser les endpoints protégés :

1. Créez un utilisateur via Tinker ou un seeder
2. Générez un token d'accès
3. Utilisez le token dans l'en-tête `Authorization: Bearer <token>`

### Variables d'environnement importantes
```env
APP_NAME="API Gestion Bancaire"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bank_api
DB_USERNAME=
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

## 📚 Utilisation

### Démarrage du serveur
```bash
php artisan serve
```

### Tests
```bash
php artisan test
```

### Génération de la documentation
La documentation Swagger est générée automatiquement. Visitez :
- **Swagger UI** : `http://localhost:8000/api/documentation`
- **JSON OpenAPI** : `http://localhost:8000/api/docs.json`

## 🔌 API Endpoints

### Clients
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/clients` | Lister tous les clients |
| POST | `/api/clients` | Créer un nouveau client |
| GET | `/api/clients/{id}` | Afficher un client spécifique |
| PUT | `/api/clients/{id}` | Mettre à jour un client |
| DELETE | `/api/clients/{id}` | Supprimer un client |

#### Exemple - Créer un client
```bash
curl -X POST http://localhost:8000/api/clients \
  -H "Content-Type: application/json" \
  -d '{
    "nom": "Dupont",
    "prenom": "Jean",
    "email": "jean.dupont@example.com",
    "telephone": "+221 77 123 45 67",
    "adresse": "Dakar, Sénégal"
  }'
```

### Comptes
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/v1/comptes` | Lister tous les comptes avec clients |
| GET | `/api/v1/comptes/{id}` | Récupérer un compte spécifique (admin : tous, client : seulement les siens) |
| POST | `/api/v1/comptes` | Créer un nouveau compte (avec client si nécessaire) |

#### Exemple - Lister les comptes
```bash
curl -H "Authorization: Bearer {token}" http://localhost:8001/api/v1/comptes
```

#### Exemple - Récupérer un compte spécifique
```bash
curl -X GET http://localhost:8001/api/v1/comptes/{id} \
  -H "Authorization: Bearer {token}"
```

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

## 📖 Documentation API

L'API est entièrement documentée avec OpenAPI 3.0 et Swagger UI.

### Accès à la documentation interactive
Visitez `http://localhost:8000/api/documentation` pour :
- Explorer tous les endpoints
- Voir les schémas de données
- Tester les API directement depuis le navigateur
- Consulter les exemples de requêtes/réponses

### Schémas de données

#### Client
```json
{
  "id": "uuid",
  "nom": "string",
  "prenom": "string",
  "email": "string",
  "telephone": "string",
  "created_at": "datetime",
  "updated_at": "datetime"
}
```

#### Compte
```json
{
  "id": "uuid",
  "client_id": "uuid",
  "numeroCompte": "string",
  "type": "epargne|cheque",
  "solde": "decimal",
  "statut": "actif|bloque|ferme",
  "metadata": "object",
  "created_at": "datetime",
  "updated_at": "datetime",
  "client": "Client"
}
```

#### Transaction
```json
{
  "id": "uuid",
  "compte_id": "uuid",
  "type": "depot|retrait|virement|frais",
  "montant": "decimal",
  "devise": "string",
  "description": "string",
  "dateTransaction": "datetime",
  "statut": "en_attente|validee|annulee",
  "created_at": "datetime",
  "updated_at": "datetime"
}
```

## 🏗️ Structure du projet

```
projetLaravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ClientController.php      # CRUD Clients
│   │   │   ├── CompteController.php      # Gestion Comptes
│   │   │   ├── Api/V1/CompteController.php # API Comptes
│   │   │   └── SwaggerController.php     # Documentation
│   │   ├── Requests/
│   │   │   ├── StoreClientRequest.php    # Validation Client
│   │   │   ├── CompteRequest.php         # Validation Compte
│   │   │   └── TransactionRequest.php    # Validation Transaction
│   │   └── Middleware/
│   ├── Models/
│   │   ├── Client.php                    # Modèle Client
│   │   ├── Compte.php                    # Modèle Compte
│   │   └── Transaction.php               # Modèle Transaction
│   ├── Rules/
│   │   ├── SenegalPhoneRule.php         # Validation téléphone Sénégalais
│   │   └── SenegalNciRule.php           # Validation NCI Sénégalais
│   └── Swagger/
│       └── OpenApiDocumentation.php     # Config OpenAPI
├── database/
│   ├── migrations/                       # Migrations DB
│   ├── factories/                        # Factories pour tests
│   └── seeders/                         # Seeders pour données test
├── routes/
│   ├── api.php                          # Routes API
│   └── web.php                          # Routes Web (Swagger UI)
├── resources/
│   └── views/
│       └── swagger-ui.blade.php         # Interface Swagger
├── swagger/                             # Fichiers Swagger générés
├── composer.json                        # Dépendances PHP
└── README.md                           # Ce fichier
```

## 🧪 Tests

### Exécuter les tests
```bash
php artisan test
```

### Tests disponibles
- Tests unitaires pour les modèles
- Tests de fonctionnalités pour les contrôleurs
- Tests d'intégration pour les API

### Exemple de test
```bash
php artisan test --filter=ClientControllerTest
```

## 🔒 Sécurité

- **Validation des données** : Utilisation de Request Classes personnalisées avec règles de validation spécifiques (téléphone et NCI Sénégalais)
- **Authentification** : Laravel Sanctum pour la protection des routes
- **CSRF Protection** : Activée pour les formulaires web
- **Rate Limiting** : Configurable via middleware (60 requêtes par minute)
- **SQL Injection** : Prévention via Eloquent ORM
- **Validation avancée** : Règles personnalisées pour formats Sénégalais (téléphone +221, NCI 13 chiffres)

## 📋 Validations Personnalisées

L'API inclut des règles de validation personnalisées pour les données spécifiques au Sénégal :

### Règle Téléphone Sénégalais
- Format : `+221` suivi de 9 chiffres
- Exemple : `+221771234567`
- Utilisée dans : Création de client/compte

### Règle NCI (Carte d'Identité)
- Format : 13 chiffres
- Exemple : `1234567890123`
- Utilisée dans : Création de client/compte

## 🚀 Déploiement

### Production
1. Configurez les variables d'environnement pour la production
2. Optimisez l'autoloader : `composer install --optimize-autoloader --no-dev`
3. Cachez la configuration : `php artisan config:cache`
4. Cachez les routes : `php artisan route:cache`

### Serveur recommandé
- Apache/Nginx avec PHP 8.1+
- Base de données MySQL 8.0+ ou PostgreSQL 13+
- SSL/TLS activé

## 🤝 Contribution

1. Fork le projet
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## 📄 License

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 📞 Support

Pour toute question ou support :
- Créez une issue sur GitHub
- Contactez l'équipe de développement

---

**Développé avec ❤️ en Laravel**
