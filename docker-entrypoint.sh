#!/bin/bash

# Exécuter les migrations
php artisan migrate --force

# Exécuter les seeders
php artisan db:seed --force

# Démarrer Apache
apache2-foreground