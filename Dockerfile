FROM php:8.2-fpm

# Install system dependencies & PostgreSQL driver libraries
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    nginx

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy all project files
COPY . .

# Environment variable to allow composer as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Run composer install with verbose output and bypass platform reqs
RUN composer install --no-dev --optimize-autoloader --no-scripts --ignore-platform-reqs --no-interaction -vvv

# Set permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Create storage symlink
RUN php artisan storage:link || true

EXPOSE 8000

CMD php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=8000