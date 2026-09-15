FROM php:8.2-apache
 
# Enable the MySQL extension PHP needs to talk to the database
RUN docker-php-ext-install mysqli
 
# Fix Apache MPM conflict - PHP requires prefork, not event/worker
RUN a2dismod mpm_event mpm_worker || true && a2enmod mpm_prefork
 
# Copy all project files into Apache's web root
COPY . /var/www/html/
 
# Make sure Apache can read/write the files (needed for uploads folder)
RUN chown -R www-data:www-data /var/www/html
 
EXPOSE 80
 
