FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql zip bcmath

# Set working directory
WORKDIR /var/www/html

# Copy the application
COPY . .

# Install PHP dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Install JS dependencies & build assets
RUN npm install
RUN npm run build

# Configure Nginx
COPY ./docker/nginx.conf /etc/nginx/http.d/default.conf

# Give permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Start Nginx and PHP-FPM
CMD php artisan config:cache && php artisan route:cache && php-fpm -D && nginx -g "daemon off;"
