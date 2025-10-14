# Imagen base de PHP con Apache
FROM php:8.2-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar los archivos del proyecto al servidor
COPY . /var/www/html/

# Configurar Apache para escuchar el puerto dinámico asignado por Railway
ENV PORT=8080
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Exponer el puerto
EXPOSE ${PORT}

# Comando de inicio
CMD ["apache2-foreground"]
