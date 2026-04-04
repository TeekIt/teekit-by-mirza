FROM php:8.3-fpm

# Arguments defined in docker-compose.yml
ARG user=mirza
ARG uid=1000

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libfreetype-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    unzip \
    curl \
    git \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libssl-dev \
    zip 

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install pdo pdo_mysql mysqli mbstring exif pcntl zip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u ${uid} -d /home/${user} ${user} \
    && mkdir -p /home/${user}/.composer \
    && chown -R ${user}:${user} /home/${user}

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Add PHP configuration
RUN echo "memory_limit=1G" > /usr/local/etc/php/conf.d/memory-limit.ini
RUN echo "max_execution_time=300" > /usr/local/etc/php/conf.d/max-execution-time.ini

WORKDIR /var/www/teekit-by-mirza

# Copy and set permissions for entrypoint script
COPY docker-compose/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

USER ${user}

ENTRYPOINT ["entrypoint.sh"]