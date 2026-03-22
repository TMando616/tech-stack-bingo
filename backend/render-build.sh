#!/usr/bin/env bash
# exit on error
set -o errexit

echo "--- Building Laravel ---"
composer install --no-dev --optimize-autoloader

echo "--- Running Migrations ---"
# --force is required for production
php artisan migrate --force

echo "--- Optimizing ---"
php artisan config:cache
php artisan route:cache
php artisan view:cache
