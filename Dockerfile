FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (including intl for NumberFormatter)
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip intl

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
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views 2>/dev/null || true
RUN chmod -R 777 storage bootstrap/cache 2>/dev/null || true

# Mark as installed (skip installer)
RUN touch storage/installed && chmod 777 storage/installed

# Clear any cached config from build
RUN php artisan config:clear 2>/dev/null || true
RUN php artisan cache:clear 2>/dev/null || true
RUN php artisan route:clear 2>/dev/null || true
RUN php artisan view:clear 2>/dev/null || true

# Expose port
EXPOSE 8000

# CMD is overridden by railway.json startCommand
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8000} -t public public/router.php"]

