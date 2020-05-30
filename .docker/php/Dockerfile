FROM php:7.3-apache

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

RUN apt-get update
RUN apt-get install -y wget git libzip-dev unzip libyaml-dev

RUN pecl install xdebug-2.9.5; docker-php-ext-enable xdebug
RUN echo 'xdebug.remote_enable=1' >> $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini; \
    echo 'xdebug.remote_host=host.docker.internal' >> $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini; \
    echo 'xdebug.remote_port=9000' >> $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini; \
    echo 'xdebug.var_display_max_depth=10' >> $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini; \
    echo 'xdebug.cli_color=1' >> $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini; \
    echo 'xdebug.show_local_vars=1' >> $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini
RUN docker-php-ext-install zip
RUN a2enmod headers
RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/4d7f8d40f9788de07c7f7b8946f340bf89535453/web/installer -O - -q | php -- --install-dir=/usr/bin --filename=composer

RUN pecl install yaml-2.1.0; docker-php-ext-enable yaml
