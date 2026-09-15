FROM php:8.2-apache

# Enable the MySQL extension PHP needs to talk to the database
RUN docker-php-ext-install mysqli

# Remove all conflicting MPM modules, keep only prefork (required by PHP)
RUN rm -f /etc/apache2/mods-enabled/mpm_event.conf /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_worker.conf /etc/apache2/mods-enabled/mpm_worker.load

# Copy all project files into Apache's web root
COPY . /var/www/html/

# Make sure Apache can read/write the files (needed for uploads folder)
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
