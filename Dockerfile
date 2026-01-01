FROM php:8.3-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid

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
	&& docker-php-ext-install pdo pdo_mysql mysqli mbstring exif pcntl gd zip
	
# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Add PHP configuration
RUN echo "memory_limit=1G" > /usr/local/etc/php/conf.d/memory-limit.ini
RUN echo "max_execution_time=300" > /usr/local/etc/php/conf.d/max-execution-time.ini

# Configure PHP-FPM to listen on all interfaces (for Docker networking)
# RUN sed -i 's/listen = 127.0.0.1:9000/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.d/www.conf || \
#     echo "listen = 0.0.0.0:9000" >> /usr/local/etc/php-fpm.d/www.conf

# # Ensure PHP-FPM runs workers as www-data (for security)
# RUN sed -i 's/user = www-data/user = www-data/' /usr/local/etc/php-fpm.d/www.conf && \
#     sed -i 's/group = www-data/group = www-data/' /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/teekit-by-mirza

# Copy and set permissions for entrypoint script
COPY docker-compose/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

USER $user

ENTRYPOINT ["entrypoint.sh"]