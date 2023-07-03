FROM php:8.2-fpm

# Set the application environment that paste from command line
ARG APP_ENV

# Set the application environment
ENV APP_ENV $APP_ENV

# Set the working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Override with custom php.ini configuration
COPY ./docker/php-fpm/php-ini-overrides.ini $PHP_INI_DIR/conf.d/99-overrides.ini

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the application files to the container
COPY . .

# Install application dependencies
RUN composer install --no-dev --no-interaction --optimize-autoloader

# copy .env file from environment variables: .env.development, .env.production, ...
COPY .env.example .env

# Generate application key
RUN php artisan key:generate

# Generate JWT secret
RUN php artisan jwt:secret

# Set permissions for the storage and bootstrap/cache directories
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Install nginx
RUN apt-get update && \
    apt-get install -y nginx

# Remove the default Nginx configuration file
RUN rm /etc/nginx/sites-enabled/default

# Copy our Nginx configuration file
COPY ./docker/nginx/nginx.conf /etc/nginx/sites-enabled/

# Expose port 80 and start Nginx and PHP-FPM servers
EXPOSE 80

CMD ["/bin/bash", "-c", "php-fpm -D && nginx -g \"daemon off;\""]