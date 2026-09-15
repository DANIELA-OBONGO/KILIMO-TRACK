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


FROM php:8.2-apache
 
# Enable the MySQL extension PHP needs to talk to the database
RUN docker-php-ext-install mysqli
 
# DIAGNOSTIC: show exactly which MPM modules are enabled before we touch anything
RUN echo "===BEFORE===" && ls -la /etc/apache2/mods-enabled/ | grep -i mpm
 
# Try to disable the conflicting ones and enable only prefork (required by mod_php)
RUN a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork 2>/dev/null; true
 
# DIAGNOSTIC: show what's enabled after our fix attempt
RUN echo "===AFTER===" && ls -la /etc/apache2/mods-enabled/ | grep -i mpm
 
# Copy all project files into Apache's web root
COPY . /var/www/html/
 
# Make sure Apache can read/write the files (needed for uploads folder)
RUN chown -R www-data:www-data /var/www/html
 
EXPOSE 80