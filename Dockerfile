# =====================================================================
# Axiora — imagem de produção (PHP 8.2 + Apache), pronta para o Coolify.
# O projecto abre na raiz (sem /public): o .htaccess bloqueia app/, core/,
# config/, database/, storage/... e encaminha tudo para o index.php.
# =====================================================================

# ---- 1. CSS do painel (Tailwind) ------------------------------------
FROM node:20-alpine AS assets
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY tailwind.config.js ./
COPY src ./src
COPY app/Views ./app/Views
COPY assets/js ./assets/js
RUN npx tailwindcss -i ./src/input.css -o ./assets/css/admin.css --minify

# ---- 2. Aplicação ---------------------------------------------------
FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql opcache \
    && a2enmod rewrite headers expires deflate remoteip

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-axiora.ini
COPY docker/apache.conf /etc/apache2/conf-available/axiora.conf
RUN a2enconf axiora

WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
COPY --from=assets --chown=www-data:www-data /build/assets/css/admin.css ./assets/css/admin.css

COPY docker/entrypoint.sh /usr/local/bin/axiora-entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/axiora-entrypoint \
    && chmod +x /usr/local/bin/axiora-entrypoint \
    && mkdir -p assets/uploads storage/ratelimit storage/sessoes \
    && chown -R www-data:www-data assets/uploads storage

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD php -r "exit(@file_get_contents('http://127.0.0.1/saude') === false ? 1 : 0);"

ENTRYPOINT ["axiora-entrypoint"]
# -DAXIORA_DOCKER: o .htaccess deixa o redireccionamento para HTTPS ao proxy (Traefik do Coolify).
CMD ["apache2-foreground", "-DAXIORA_DOCKER"]
