# Imagen base de PHP con Apache
FROM php:8.2-apache

# Instalar extensiones necesarias para MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitar mod_rewrite (por si usas .htaccess)
RUN a2enmod rewrite

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto al contenedor
COPY . .

# Exponer puerto 8080 (Railway lo detecta automáticamente)
EXPOSE 8080

# Usamos /bin/sh -c para que Railway sustituya correctamente la variable $PORT
CMD ["/bin/sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t ."]
