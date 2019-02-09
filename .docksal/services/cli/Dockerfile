FROM docksal/cli:2.4-php7.1

RUN apt-get update >/dev/null; \
        apt-get -y --no-install-recommends install >/dev/null \
        libjpeg-dev \
        libpng-dev \
        libvpx-dev \
        libxpm-dev \
        libfreetype6-dev \
        libwebp-dev \
        webp; \
docker-php-ext-configure >/dev/null gd \
        --with-freetype-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/ \
        --with-webp-dir=/usr/include/ \
        --with-png-dir=/usr/include/ \
        --with-xpm-dir=/usr/include/; \
docker-php-ext-install gd;
