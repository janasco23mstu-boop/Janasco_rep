FROM php:8.2-apache

RUN docker-php-ext-install mysqli \
    && sed -i 's/^Listen 80$/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:10000>/' /etc/apache2/sites-available/000-default.conf \
    && printf '\nServerName localhost\n' >> /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . /var/www/html/

RUN mkdir -p /var/www/html/images/users /var/www/html/images/instructors \
    && chown -R www-data:www-data /var/www/html/images \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

RUN printf 'file_uploads=On\nupload_max_filesize=5M\npost_max_size=6M\nmax_file_uploads=20\n' > /usr/local/etc/php/conf.d/uploads.ini

EXPOSE 10000
