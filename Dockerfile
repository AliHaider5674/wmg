
 
FROM php:8.1-fpm AS builder
# system package installation
WORKDIR /root/
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
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libpng-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) gd \
  && rm -rf /var/lib/apt/lists/*
# PHP extension installation
RUN docker-php-ext-install zip soap sodium mysqli pdo pdo_mysql gettext
# composer installation
RUN wget https://getcomposer.org/download/1.10.20/composer.phar  \
  && mv composer.phar /usr/local/bin/composer \
  && chmod +x /usr/local/bin/composer
# Add application to image last minute to prevent unnecessary image rebuild
#COPY --chown=www-data --from=builder /app/ /var/www/html/
#COPY /public/index.php /var/www/html/
#RUN chown www-data:www-data -R /var/www/html/app/
# the other kind of composer installation
#RUN cd /app && composer install
RUN mkdir -p /app
COPY . /app/
RUN chown www-data:www-data -R /app/
#RUN cd /app && composer install
#RUN systemctl restart nginx


FROM ubuntu:20.04 AS nginx_build
RUN apt-get update \
    && apt-get install -y nginx \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && echo "daemon off;" >> /etc/nginx/nginx.conf
COPY --chown=www --from=builder /app  /app

COPY --chown=www --from=builder /app /var/www/html/app/
COPY --from=builder /app/default /etc/nginx/conf.d/
COPY --from=builder /app/default /etc/nginx/sites-enabled/
COPY --from=builder /app/default /etc/nginx/sites-available/
EXPOSE 80
CMD ["nginx"]