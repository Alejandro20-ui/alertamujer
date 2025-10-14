# Imagen base de PHP con Apache
FROM php:8.2-apache

# Instalar extensiones necesarias para MySQL
RUN docker-php-ext-install mysqli

# Copiar archivos de tu app
COPY . /var/www/html/

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Exponer el puerto (Railway necesita saberlo)
EXPOSE 8080

# Iniciar Apache en el puerto que Railway asigna
CMD apachectl -D FOREGROUND
