# Utiliser l'image PHP 8.3 avec Apache
FROM php:8.3-apache

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm

# Installer les extensions PHP nécessaires pour Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers du projet
COPY . .

# Copier et rendre le script d'entrée exécutable
RUN cp docker-entrypoint.sh /usr/local/bin/ && chmod +x /usr/local/bin/docker-entrypoint.sh

# Configurer Git pour éviter les problèmes de ownership
RUN git config --global --add safe.directory /var/www/html

# Installer les dépendances PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Installer les dépendances Node.js si nécessaire (pour Vite)
RUN npm install && npm run build

# Configurer les permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage/logs

# Copier le fichier .env.example vers .env si .env n'existe pas
RUN cp .env.example .env || true

# Générer la clé d'application Laravel
RUN php artisan key:generate

# Configurer Apache pour servir depuis le répertoire public
RUN echo '<VirtualHost *:80>\n    DocumentRoot /var/www/html/public\n    <Directory /var/www/html/public>\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Activer le module rewrite
RUN a2enmod rewrite

# Exposer le port 80
EXPOSE 80

# Point d'entrée
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]