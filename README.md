# Question Service

Простой сервис вопросов-ответов. Разрабатывается в целях изучения фреймворка Symfony.

## Используемые технологии в проекте

* Язык разработки: `PHP 7.4`
* Веб сервер: `nginx + php-fpm`
* Фреймворк: `Symfony 5.2`
* База данных: `PostgreSQL 11`
* Под кэширование: `Redis`
* Поисковой движок: `ElasticSearch 7.10`
* Очередь сообщений: `в базе данных` (опционально)
* Управление процессами воркеров: `supervisor`
* Оптимизация загружаемых изображений: `jpegoptim + optipng`
* Сборка ресурсов фронта: `yarn`

## Установка и настройка приложения

* Клонировать репозиторий:
```text
git clone git@github.com:webspec2012/question-service.ru.git .
```

* Создать базу данных и загрузить в неё дамп
```text
pg_restore -h 127.0.0.1 -U db_username -F p -d db_name ./docs/postgresql/dump.sql
```

* Копировать файл `.env` в `.env.local` и произвести необходимые настройки

* Установить зависимости

```text
composer install --no-dev --optimize-autoloader
composer dump-autoload --optimize --no-dev --classmap-authoritative
npm install
yarn install
```

* Сделать необходимые настройки nginx хостов по аналогии с примером в `/docs/nginx/nginx.conf`.

* Настроить `supervisor` для запуска воркеров очереди в режиме демона по аналогии с примером `/docs/supervisor/example.ini`.

После чего запустите их:
```text
supervisorctl reread
supervisorctl update
supervisorctl start question-service-consume-async-prod:*
```

## Минимальные требования
```text
jpegoptim/optipng
supervisor
PostgreSQL 11 и выше
ElasticSearch 7.x
npm 7.4 и выше
yarn 1.22 и выше
php 7.4 и выше
php-fpm
php-cli
php-gd
php-intl
php-json
php-mbstring
php-pdo
php-pgsql
php-sodium
php-xml
php-sysvsem
php-opcache
```
