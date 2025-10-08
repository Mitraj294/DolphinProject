# Use official PHP image with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions in a single layer
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html

# Set correct permissions, install PHP dependencies, enable mod_rewrite, and create storage dirs
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && a2enmod rewrite

COPY docker-apache.conf /etc/apache2/sites-available/000-default.conf

# Create storage directories if they don't exist
RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs

# Expose port 80
EXPOSE 80

# Add entrypoint script (will be copied in the build)
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Use the entrypoint script (exec form) to run startup tasks and then apache
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
