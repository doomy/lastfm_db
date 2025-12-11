FROM php:8.2-apache

# System deps for common PHP extensions
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    libzip-dev \
  && rm -rf /var/lib/apt/lists/*

# Enable Apache modules (optional but often useful)
RUN a2enmod rewrite headers

# PHP extensions needed by this app
RUN docker-php-ext-install mysqli

# App will be bind-mounted in docker-compose to /var/www/html
WORKDIR /var/www/html
