FROM php:8.0-apache

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y \
    ca-certificates \
    zlib1g-dev \
    libpng-dev \
    libzip-dev \
    git \
    unzip \
    zip \
    software-properties-common \
    npm \
    && update-ca-certificates \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
    bcmath \
    exif \
    gd \
    mysqli \
    pdo \
    pdo_mysql \
    zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json composer.lock ./
RUN /usr/local/bin/composer install --no-scripts --no-autoloader
RUN /usr/local/bin/composer dump-autoload --optimize

RUN npm install -g npm@latest n nodemon browser-sync
RUN n latest

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

CMD sh -c "nodemon --exec 'apache2-foreground' & browser-sync start --proxy 'localhost:80' --files '**/*.php' --files '**/*.html' --files '**/*.js'"