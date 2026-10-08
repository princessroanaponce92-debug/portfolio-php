FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql && a2enmod rewrite

 ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
 && printf '<Directory /var/www/html/public>\n  AllowOverride All\n  Require all granted\n</Directory>\n' > /etc/apache2/conf-available/app.conf \
 && a2enconf app

 ENV PORT=10000
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
 && sed -i 's/:80>/:${PORT}>/' /etc/apache2/sites-available/000-default.conf

 RUN printf 'upload_max_filesize=5M\npost_max_size=8M\ndisplay_errors=Off\nlog_errors=On\n' > /usr/local/etc/php/conf.d/app.ini

COPY . /var/www/html
