FROM php:8.1-cli

RUN apt-get update \
    && apt-get install -y \
        libzip-dev \
    && docker-php-ext-install zip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app