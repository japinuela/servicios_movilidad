# Imagen base segura y específica
FROM php:8.4.6-cli-alpine

# Instalar dependencias del sistema necesarias para PDO y Composer
RUN apk add --no-cache \
    libzip-dev \
    mariadb-dev \
    unzip \
    curl \
    bash

# Instalar extensiones necesarias de PHP
RUN docker-php-ext-install pdo pdo_mysql

# Instalar Composer (última versión estable)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer el directorio de trabajo
WORKDIR /var/www

# Copiar el código de la aplicación (opcional si usa volumen en Compose)
COPY . .

# Exponer el puerto para el servidor embebido
EXPOSE 8080

# Comando por defecto al iniciar el contenedor
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
