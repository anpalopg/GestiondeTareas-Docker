FROM php:8.2-apache

# Instalar extensiones necesarias de PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar todo el proyecto al contenedor
COPY . /var/www/html

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Ajustar permisos de los archivos para que Apache pueda leerlos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configurar ServerName para Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Exponer puerto 80
EXPOSE 80
