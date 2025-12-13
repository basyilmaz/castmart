FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy all files
COPY . .

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts

# Install npm dependencies and build
RUN npm install && npm run build

# Generate APP_KEY if not exists (for initial setup)
RUN if [ -z "$APP_KEY" ]; then php artisan key:generate --force || true; fi

# Set permissions
RUN chmod -R 775 storage bootstrap/cache 2>/dev/null || true
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views 2>/dev/null || true
RUN chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Create storage link
RUN php artisan storage:link 2>/dev/null || true

# Expose port
EXPOSE 8000

# Simple start - no migration (will be done manually or via Railway command)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
