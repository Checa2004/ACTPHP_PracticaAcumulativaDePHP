FROM php:8.5-apache-bookworm

# Instalar dependencias necesarias para PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PDO
RUN docker-php-ext-install pdo_pgsql pdo_mysql

# Copiar y activar configuración de Apache
COPY apache/jukebox.conf /etc/apache2/sites-available/jukebox.conf
RUN a2dissite 000-default.conf && a2ensite jukebox.conf