#/**
# * TangoMan symfony-php-fpm.dockerfile
# *
# * Symfony 5 Dockerfile
# *
# * @version  0.1.0
# * @author   "Matthias Morin" <mat@tangoman.io>
# * @license  MIT
# */

# based on Debian 10 (buster)
FROM php:7.4-fpm

WORKDIR /www

EXPOSE 80

# persistent / runtime deps
RUN echo "\033[1;32m Install Persistent / Runtime Deps \033[0m" \
    && apt-get update \
    && apt-get install -y --no-install-recommends apt-utils git make unzip vim \
    && cp /etc/skel/.bashrc /root \
    && cp /etc/skel/.profile /root

# Install TangoMan .bash_aliases
RUN echo "\033[1;32m Install TangoMan bash_aliases \033[0m" \
    && git clone --depth 1 https://github.com/TangoMan75/bash_aliases /root/bash_aliases \
    && cd /root/bash_aliases \
    && make min-install

# Install symfony PHP Core extensions dependencies (amqp gd intl pdo_mysql pdo_pgsql xsl zip)
# https://github.com/mlocati/docker-php-extension-installer
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/
RUN echo "\033[1;32m Install PHP Extensions \033[0m" \
    && chmod uga+x /usr/local/bin/install-php-extensions \
    && sync \
    && install-php-extensions intl zip

# PHP configuration
COPY ./conf.d/symfony-prod.ini /usr/local/etc/php/conf.d/custom.ini

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
# Install Composer and Symfony CLI
RUN echo "\033[1;32m Install Composer / Symfony CLI \033[0m" \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer self-update --stable \
    && curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony/bin/symfony /usr/local/bin/symfony

# Install Symfony Flex globally to speed up download of Composer packages (parallelized prefetching)
RUN echo "\033[1;32m Install Symfony Flex Globally \033[0m" \
    && set -eux; \
    composer global require "symfony/flex" --prefer-dist --no-progress --no-suggest --classmap-authoritative; \
    composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

# Allow the Symfony application to write inside volumes
RUN echo "\033[1;32m Allow Symfony application to write inside volumes \033[0m" \
    && mkdir -p /www/var/ && chown -R www-data /www/var/

# Start php-fpm
CMD [ "php-fpm" ]
