# Imagen base con PHP + Apache
FROM php:8.2-apache

# Instalar extensión mysqli (necesaria para MySQL)
RUN docker-php-ext-install mysqli

# Habilitar mod_rewrite y configurar ServerName
RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configurar el documento raíz
WORKDIR /var/www/html
COPY . .

# Configurar Apache para escuchar el puerto dinámico de Railway
# Esto es CLAVE para evitar el error 502
RUN sed -i "s/Listen 80/Listen \${PORT}/" /etc/apache2/ports.conf

# Exponer el puerto (Railway lo maneja internamente)
EXPOSE 8080

# Comando para mantener Apache corriendo
CMD ["apache2-foreground"]
