# Use official PHP 8.2 image with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install composer
RUN apt-get update && apt-get install -y unzip git \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Install dependencies (ignore dev packages)
RUN composer install --no-dev --optimize-autoloader

# Expose port 10000 (Render uses this internally)
EXPOSE 10000

# Start PHP's built-in server, serving the /public directory
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
