#!/bin/sh
set -e

# Ensure persistent dirs exist with correct ownership (volumes might be empty/new)
mkdir -p /data /uploads
chown -R www-data:www-data /data /uploads
chmod -R 775 /data /uploads

# Drop privileges happens inside php-fpm/nginx via their own configs.
exec "$@"
