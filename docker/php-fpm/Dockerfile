FROM php:8.2-fpm-alpine

# Install required packages
RUN set -eux; \
    umask 022; \
    apk add --update --no-cache \
        # Required tools and commands \
        bash \
        git \
        less \
        zip \
        # Required library for PHP Extensions
        icu-libs \
        libzip \
        libxslt

# Install dev tools and install PHP extensions
RUN set -eux; \
    umask 022; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libzip-dev \
        libxml2-dev \
        libxslt-dev \
    ; \
    # Install PHP extensions
    docker-php-source extract; \
    docker-php-ext-install bcmath intl xsl zip; \
    docker-php-source delete; \
    \
    # Install composer
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"; \
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '$(wget -q -O - https://composer.github.io/installer.sig)') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"; \
    php composer-setup.php --filename=composer; \
    php -r "unlink('composer-setup.php');"; \
    mv composer /usr/local/bin/composer; \
    \
    # Clear all dev tools
    apk del --no-network .build-deps; \
    rm -rf /var/lib/apt/lists/*; \
    rm -rf /tmp/* ~/.pearrc

EXPOSE 9000
CMD ["php-fpm"]
