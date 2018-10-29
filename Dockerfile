FROM php:7.2-fpm-alpine

# ENV PHP_EXTRA_CONFIGURE_ARGS --enable-fpm --with-fpm-user=www-data --with-fpm-group=www-data --disable-cgi
ENV PHP_EXTRA_CONFIGURE_ARGS --enable-fpm --with-fpm-user=root --with-fpm-group=root --disable-cgi

RUN \
    # echo "151.101.108.249 dl-cdn.alpinelinux.org" >> /etc/hosts \
    docker-php-ext-install mysqli && docker-php-ext-enable mysqli