FROM php:8.3-cli-bookworm

# 1) Sistema e libs
RUN apt-get update && apt-get install -y \
        git \
        curl \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        zip \
        unzip \
        libpq-dev \
        libzip-dev \
        libicu-dev \
    libsqlite3-dev \
        ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# 2) Extensões PHP necessárias (inclui Postgres)
RUN docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
             pdo_pgsql pdo_sqlite sqlite3 bcmath mbstring intl zip gd

# 3) Node.js 20 para build do front-end
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# 4) Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 5) Diretório de trabalho
WORKDIR /var/www/html

# 6) Copiar manifests primeiro (melhor cache)
COPY composer.json composer.lock* package.json package-lock.json* ./

# 7) Instalar dependências
RUN composer install --no-dev --prefer-dist --no-scripts --optimize-autoloader \
    && npm ci || npm install

# 8) Copiar restante do projeto
COPY . .

# 9) Build dos assets e limpeza
RUN npm run build \
    && rm -rf node_modules

# 10) Permissões para cache/logs
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# 11) Copiar entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# 12) Porta dinâmica (Railway usa $PORT)
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
