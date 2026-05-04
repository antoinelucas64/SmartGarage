# Single-container image: nginx + PHP-FPM + supervisord on Alpine
FROM php:8.3-fpm-alpine

# Install nginx, supervisord, build deps
RUN apk add --no-cache \
        nginx \
        supervisor \
        sqlite \
        sqlite-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/cache/apk/*

# Configure PHP
RUN { \
        echo 'expose_php = Off'; \
        echo 'upload_max_filesize = 16M'; \
        echo 'post_max_size = 16M'; \
        echo 'memory_limit = 128M'; \
        echo 'session.cookie_httponly = 1'; \
        echo 'session.cookie_samesite = "Lax"'; \
        echo 'session.use_strict_mode = 1'; \
    } > /usr/local/etc/php/conf.d/app.ini

# nginx & php-fpm sockets
RUN mkdir -p /run/nginx /var/log/supervisord

# Copy configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copy app
WORKDIR /app
COPY --chown=www-data:www-data schema.sql ./
COPY --chown=www-data:www-data src ./src
COPY --chown=www-data:www-data public ./public

# Persistent dirs (mounted as volumes)
RUN mkdir -p /data /uploads \
    && chown -R www-data:www-data /data /uploads /app \
    && chmod -R 775 /data /uploads

ENV DATA_DIR=/data \
    UPLOADS_DIR=/uploads \
    APP_DEBUG=0

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD wget -qO- http://127.0.0.1:8080/ > /dev/null || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
