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
    npm \
    sqlite3 \
    libsqlite3-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip

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

# Set permissions
RUN chmod -R 777 storage bootstrap/cache 2>/dev/null || true
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views database 2>/dev/null || true
RUN chmod -R 777 storage bootstrap/cache database 2>/dev/null || true

# Create SQLite database
RUN touch database/database.sqlite && chmod 777 database/database.sqlite

# Mark as installed (skip installer)
RUN touch storage/installed && chmod 777 storage/installed

# Create storage link
RUN php artisan storage:link 2>/dev/null || true

# Clear config cache
RUN php artisan config:clear 2>/dev/null || true

# Run migrations
RUN php artisan migrate --force 2>/dev/null || true

# Seed the database with basic data
RUN php artisan db:seed --force 2>/dev/null || true

# Expose port
EXPOSE 8000

# Stay in /var/www and use PHP built-in server with document root
CMD ["sh", "-c", "cd /var/www && php -S 0.0.0.0:${PORT:-8000} -t public public/index.php"]
