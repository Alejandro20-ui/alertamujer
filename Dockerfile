# Usa una imagen de PHP con Apache y soporte MySQL
FROM php:8.2-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar todos los archivos del proyecto al servidor Apache
COPY . /var/www/html/

# Exponer el puerto 80 (el que usa Railway por defecto)
EXPOSE 80
