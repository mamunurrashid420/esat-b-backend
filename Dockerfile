# Use an official PHP runtime as a parent image
FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and clean up after installation to reduce image size
RUN apt-get update && apt-get install -y \
    libpng-dev \
    zip \
    unzip \
    git \
    curl \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer from a stable, specific version
COPY --from=composer:2.1 /usr/bin/composer /usr/bin/composer

# Create a non-root user and set the appropriate permissions
RUN useradd -m -u 1000 appuser \
    && chown -R appuser:appuser /var/www/html

# Set the user to run the application as a non-root user
USER appuser

# Copy the existing application
COPY . .

# Set permissions for Laravel (make sure only the necessary directories are writable)
RUN chown -R appuser:appuser /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Expose port
EXPOSE 9000

# Start PHP-FPM service
CMD ["php-fpm"]
