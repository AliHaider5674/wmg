FROM php:7.4-fpm

# system package installation
RUN apt-get update ; apt-get install -y \
  gettext-base \
  git \
  libsodium-dev \
  libxml2-dev \
  libzip-dev \
  sudo \
  wget \
  vim \
  zip \
  && rm -rf /var/lib/apt/lists/*

# PHP extension installation
RUN docker-php-ext-install zip soap sodium mysqli pdo pdo_mysql gettext

# composer installation
RUN wget https://getcomposer.org/download/1.10.20/composer.phar  \
  && mv composer.phar /usr/local/bin/composer \
  && chmod +x /usr/local/bin/composer

# Add application to image last minute to prevent unnecessary image rebuild
COPY . /app
RUN chown www-data:www-data -R /app

# the other kind of composer installation
RUN cd /app && composer install
