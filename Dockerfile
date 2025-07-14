# Use official PHP 8.2 Alpine image
FROM php:8.2-apache-alpine

# Set working directory
WORKDIR /var/www/html

# Install necessary PHP extensions for MySQL
RUN apk add --no-cache \
    mysql-client \
    && docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Copy application files
COPY . /var/www/html/

# Create images directory with proper permissions
RUN mkdir -p /var/www/html/images/driveway && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Configure Apache
RUN echo '<Directory /var/www/html>' > /etc/apache2/conf-available/timeline.conf && \
    echo '    AllowOverride All' >> /etc/apache2/conf-available/timeline.conf && \
    echo '    Require all granted' >> /etc/apache2/conf-available/timeline.conf && \
    echo '</Directory>' >> /etc/apache2/conf-available/timeline.conf && \
    a2enconf timeline

# Create .htaccess for better security and clean URLs
RUN echo 'DirectoryIndex index.php' > /var/www/html/.htaccess && \
    echo 'Options -Indexes' >> /var/www/html/.htaccess && \
    echo 'RewriteEngine On' >> /var/www/html/.htaccess && \
    echo 'RewriteCond %{REQUEST_FILENAME} !-f' >> /var/www/html/.htaccess && \
    echo 'RewriteCond %{REQUEST_FILENAME} !-d' >> /var/www/html/.htaccess && \
    echo 'RewriteRule ^api/(.*)$ api/$1 [L]' >> /var/www/html/.htaccess

# Expose port 80
EXPOSE 80

# Set environment variables (can be overridden at runtime)
ENV DB_HOST=localhost
ENV DB_NAME=timeline_db
ENV DB_USER=root
ENV DB_PASS=password

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Start Apache in foreground
CMD ["apache2-ctl", "-D", "FOREGROUND"]