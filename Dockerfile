FROM php:8.2-apache

# Cài extension
RUN docker-php-ext-install pdo pdo_mysql

# Copy project vào container
COPY . /var/www/html

# Chuyển working dir về Laravel
WORKDIR /var/www/html

# Copy VirtualHost config nếu cần
# COPY ./apache.conf /etc/apache2/sites-available/000-default.conf

# Enable rewrite
RUN a2enmod rewrite

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Start Apache
CMD ["apache2-foreground"]

