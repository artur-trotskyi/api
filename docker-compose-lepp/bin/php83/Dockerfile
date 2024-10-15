FROM php:8.3.12-fpm

# Suppresses debconf complaints of trying to install apt packages interactively
# Reference: https://github.com/moby/moby/issues/4032#issuecomment-192327844
ARG DEBIAN_FRONTEND=noninteractive

# Update package lists and upgrade installed packages
RUN apt-get -y update --fix-missing && \
    apt-get upgrade -y && \
    apt-get --no-install-recommends install -y apt-utils && \
    rm -rf /var/lib/apt/lists/*


# Install useful tools and important libraries
RUN apt-get -y update && \
    apt-get -y --no-install-recommends install nano wget dialog \
    libsqlite3-dev libsqlite3-0 \
    default-mysql-client zlib1g-dev libzip-dev libicu-dev \
    build-essential git curl libonig-dev iputils-ping \
    libcurl4 libcurl4-openssl-dev zip openssl && \
    rm -rf /var/lib/apt/lists/* && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Xdebug for debugging PHP applications
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    mkdir /var/log/xdebug

# Install Redis extension for PHP
RUN pecl install redis-6.1.0 && \
    docker-php-ext-enable redis

# Install Imagick for image manipulation
RUN apt-get update && \
    apt-get -y --no-install-recommends install --fix-missing libmagickwand-dev && \
    rm -rf /var/lib/apt/lists/*

# Clone and install Imagick from GitHub using a specific commit
# Reference: https://github.com/Imagick/imagick
ARG IMAGICK_COMMIT="28f27044e435a2b203e32675e942eb8de620ee58"

RUN cd /usr/local/src && \
    git clone https://github.com/Imagick/imagick && \
    cd imagick && \
    git checkout ${IMAGICK_COMMIT} && \
    phpize && \
    ./configure && \
    make && \
    make install && \
    cd .. && \
    rm -rf imagick && \
    docker-php-ext-enable imagick

# Install PostgreSQL development libraries and PHP extensions
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pgsql pdo_pgsql

# Install other PHP 8 extensions
RUN docker-php-ext-install pdo_mysql pdo_sqlite bcmath mysqli curl zip intl mbstring gettext calendar exif sockets


# Install FreeType and GD library for image processing
RUN apt-get -y update && \
    apt-get --no-install-recommends install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd

# Cleanup temporary files and source code to reduce image size
RUN rm -rf /usr/src/*

# Install additional utilities like unzip and p7zip
RUN apt-get update && \
    apt-get -y install unzip p7zip-full
