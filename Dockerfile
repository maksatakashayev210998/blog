# Используем официальный PHP 8.3 образ с FPM
FROM php:8.3-fpm

# Установка зависимостей для работы с MySQL, Redis и другими сервисами
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    g++ \
    libxml2-dev \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Установка расширений PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip intl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Установка рабочей директории
WORKDIR /var/www

# Копируем все файлы проекта в контейнер
COPY . .

# Устанавливаем права на папку с Laravel (на случай, если вы используете кеширование, логи и сессии)
RUN chown -R www-data:www-data /var/www

# Стартовый процесс будет использовать php-fpm
CMD ["php-fpm"]
