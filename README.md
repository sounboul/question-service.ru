# Question Service

Простой сервис вопросов-ответов. Разрабатывается в целях изучения фреймворка Symfony.

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
```

# Установка и настройка

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
 # Сборка assets

Происходит черед yarn.

```
yarn run encore [dev|prod]
```
