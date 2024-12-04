FROM php:8.2-apache

# Update and install necessary system dependencies first
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    libpng-dev \
    libjpeg-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    libonig-dev \
    libmcrypt-dev \
    libmariadb-dev \
    && apt-get clean

# Install PHP extensions using docker-php-ext-install
RUN docker-php-ext-install \
    bcmath \
    curl \
    mbstring \
    xml \
    zip \
    mysqli


# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY ./api/composer.json /var/www/html/composer.json
WORKDIR /var/www/html
RUN composer install

# Clean up unnecessary files to keep the image light
RUN rm -rf /var/lib/apt/lists/*


# Expose port 8080 for the web service
EXPOSE 8080
