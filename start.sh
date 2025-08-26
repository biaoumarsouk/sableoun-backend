#!/bin/sh

# Étape 1: Optimiser l'application
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Étape 2: Préparer la base de données
echo "Running migrations..."
php artisan migrate --force

# Étape 3: Créer le lien de stockage
echo "Linking storage..."
php artisan storage:link

# Étape 4: Lancer le serveur web Apache
echo "Starting Apache server..."
vendor/bin/heroku-php-apache2 public/