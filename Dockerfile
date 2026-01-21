FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
  git \
  make \
  curl \
  unzip \
  ca-certificates \
  postgresql-client \
  libpq-dev \
  && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
  pdo \
  pdo_pgsql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /workspace
