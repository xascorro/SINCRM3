# Usar una imagen oficial de PHP con Apache
FROM php:8.3-apache

# Instalar dependencias del sistema y extensiones de PHP necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli pdo pdo_mysql

# Habilitar mod_rewrite de Apache para soportar .htaccess
RUN a2enmod rewrite headers

# Configurar el directorio de trabajo
WORKDIR /var/www/html

# Copiar el código de la aplicación al contenedor
COPY . /var/www/html

# Ajustar permisos para www-data
RUN chown -R www-data:www-data /var/www/html

# Exponer el puerto 80
EXPOSE 80
