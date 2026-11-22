# syntax=docker/dockerfile:1

# --- PHP base with the PostgreSQL driver ------------------------------------
FROM php:8.3.14-apache-bookworm AS base
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && apt-get purge -y --auto-remove libpq-dev \
    && apt-get install -y --no-install-recommends libpq5 \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html

# --- Dependencies ------------------------------------------------------------
FROM composer:2.8.9 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --no-autoloader --ignore-platform-reqs
COPY src/ src/
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

FROM composer:2.8.9 AS vendor-dev
WORKDIR /app
COPY composer.json composer.lock ./
COPY src/ src/
COPY tests/ tests/
RUN composer install --no-interaction --no-progress --no-scripts --ignore-platform-reqs

# --- Runtime image -----------------------------------------------------------
FROM base AS runtime
RUN a2enmod rewrite headers \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-cine.ini"
COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
RUN rm -rf tests docs phpunit.xml.dist && chmod +x docker/entrypoint.sh bin/console
EXPOSE 80
ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["apache2-foreground"]

# --- Test runner -------------------------------------------------------------
FROM base AS test
COPY . .
COPY --from=vendor-dev /app/vendor ./vendor
CMD ["vendor/bin/phpunit"]
