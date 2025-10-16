# Imagen base con PHP + Apache
FROM php:8.2-apache

# Instalar extensión mysqli (para MySQL)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar todos los archivos del proyecto al contenedor
WORKDIR /var/www/html
COPY . .

# Crear la carpeta de uploads y darle permisos (dentro del contenedor)
RUN mkdir -p /var/www/html/uploads && chmod -R 777 /var/www/html/uploads

# Habilitar mod_rewrite y configurar ServerName
RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configurar Apache para escuchar en el puerto 8080 (Railway usa este)
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf
RUN sed -i 's/:80>/:8080>/' /etc/apache2/sites-enabled/000-default.conf

# Exponer el puerto que Railway necesita
EXPOSE 8080

# Mantener Apache corriendo
CMD ["apache2-foreground"]
