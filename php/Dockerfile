FROM php:8.2-apache

# Installe les extensions PHP nécessaires à Symfony
RUN apt-get update \
    && apt-get install -y zlib1g-dev g++ git libicu-dev zip libzip-dev \
    && pecl install apcu \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

# Active le mode rewrite
RUN a2enmod rewrite

# Répertoire de travail du container
WORKDIR /var/www

# Installation de composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installe la Symfony CLI
RUN curl https://get.symfony.com/cli/installer | bash

# Déplace la Symfony CLI pour que les commandes soient accessibles
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony