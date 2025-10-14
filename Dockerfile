# Usar la imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalar extensiones necesarias para MySQL (soluciona "could not find driver")
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitar mod_rewrite por si usas rutas amigables
RUN a2enmod rewrite

# Establecer el directorio de trabajo dentro del contenedor
WORKDIR /var/www/html

# Copiar todos los archivos del proyecto al contenedor
COPY . .

# Exponer el puerto que usará el contenedor
EXPOSE 8080

# Comando para iniciar el servidor PHP
# Usa el puerto de entorno $PORT (si existe) o 8080 por defecto
CMD php -S 0.0.0.0:${PORT:-8080} -t .
