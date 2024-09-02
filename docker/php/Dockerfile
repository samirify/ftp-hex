FROM php:8.2-fpm

# Install system dependencies and clean cache
RUN apt-get update && apt-get install -y \
 git \
 curl \
 libpng-dev \
 libonig-dev \
 libxml2-dev \
 zip \
 unzip \
 lftp openssh-client \
 ssmtp mailutils && rm -rf /var/lib/apt/lists/* \
 sendmail

# Install PHP extensions
RUN docker-php-ext-install mbstring exif pcntl bcmath gd sockets

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions amqp

RUN echo "hostname=localhost.localdomain" > /etc/ssmtp/ssmtp.conf
RUN echo "root=info@samirify.com" >> /etc/ssmtp/ssmtp.conf
RUN echo "mailhub=mailhog:1025" >> /etc/ssmtp/ssmtp.conf

RUN echo "sendmail_path=/usr/sbin/ssmtp -t" >> /usr/local/etc/php/conf.d/php-sendmail.ini

COPY docker/php/php.ini /usr/local/etc/php/conf.d/php.ini

ENV GIT_DISCOVERY_ACROSS_FILESYSTEM=1

# Install composer
ENV COMPOSER_HOME /root/composer
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_MEMORY_LIMIT=-1
COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY ./app/ /var/www/html

# Clean up
RUN apt-get clean && \
    apt-get -y autoremove && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
