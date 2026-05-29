FROM php:8.5-fpm

ARG UID=1000
ARG GID=1000
ARG INSTALL_XDEBUG=0

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    procps \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN if [ "${INSTALL_XDEBUG}" = "1" ] ; then \
    apt-get update && apt-get install -y --no-install-recommends autoconf gcc make && \
    pecl install xdebug && docker-php-ext-enable xdebug && \
    apt-get remove -y autoconf gcc make && apt-get autoremove -y && rm -rf /var/lib/apt/lists/* ; \
    fi

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

RUN useradd -G www-data,root -u ${UID} -m appuser || true

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY docker/php/wait-for-db.sh /usr/local/bin/wait-for-db.sh
RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/wait-for-db.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["php-fpm"]
