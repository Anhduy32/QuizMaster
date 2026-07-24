FROM php:8.1-apache
COPY . /var/www/html/
# Thêm libpq-dev và cài pdo_pgsql cho PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql pdo_pgsql pgsql
EXPOSE 80
CMD ["apache2-foreground"]