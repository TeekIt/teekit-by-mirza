FROM php:8.3-fpm

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
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd \
	zip \
	pdo \
	pdo_mysql \
	mysqli \
	mbstring \
	&& apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy local directories to the current local directory of our docker image
COPY ./ ./

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
RUN composer install --optimize-autoloader

EXPOSE 8000

# Start the app using serve command
CMD [ "sh", "-c", "php artisan key:generate && php artisan serve --host=0.0.0.0 --port=8000" ]