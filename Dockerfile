# Étape 1 : Base PHP + extensions
FROM php:8.3-fpm

# Installer dépendances système
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev curl nodejs npm \
    && docker-php-ext-install pdo pdo_pgsql

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier tous les fichiers du projet
COPY . .

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Installer Node.js et dépendances frontend
RUN npm install
RUN npm run build

# Générer Swagger
RUN php artisan l5-swagger:generate

# Donner les droits sur storage et bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Exposer le port 8000
EXPOSE 8000

# Commande pour démarrer Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
