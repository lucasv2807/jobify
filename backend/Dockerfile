FROM composer:2.8 AS build

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

COPY . .

FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

# Instala nginx y extensiones PHP antes de copiar el código, así se cachea mejor
RUN apk add --no-cache nginx nginx-mod-http-headers-more \
    && docker-php-ext-install pdo pdo_mysql

# Para eliminar el header que pide el zap
RUN echo "expose_php = Off" > /usr/local/etc/php/conf.d/no_expose.ini

COPY --from=build /app ./
COPY nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p /run/nginx /run/php-fpm \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
