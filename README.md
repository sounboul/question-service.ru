# Question Service

Простой сервис вопросов-ответов. Разрабатывается в целях изучения фреймворка Symfony.

Demo: `https://question-service.ru/`

## Используемые технологии в проекте

* Язык разработки: `PHP 7.4`
* Веб сервер: `nginx + php-fpm`
* Кэширующий прокси: `Varnish 6.5`
* Фреймворк: `Symfony 5.2`
* База данных: `PostgreSQL 11`
* Под кэширование: `Redis 5`
* Поисковой движок: `ElasticSearch 7.10`
* Очередь сообщений: `в базе данных` (опционально)
* Управление процессами воркеров: `supervisor`
* Оптимизация загружаемых изображений: `jpegoptim + optipng`
* Сборка ресурсов фронта: `yarn`

Процесс обработки клиентского запроса:

`Front Proxy (nginx)` -> `Cache Proxy (varnish)` -> `Back Proxy (nginx)` -> `PHP-FPM`

`Front Proxy` не входит в состав docker-compose образа, но пример простой конфигурации представлен в файле `docker/nginx/nginx-proxy.conf`.

## Установка через docker

* Клонировать репозиторий:

```bash
mkdir /app && cd /app
git clone git@github.com:webspec2012/question-service.ru.git .
```

* Произвести необходимые настройки в файле `.env`:
```bash
cp .env.example .env
```

* Произвести необходимые настройки в файле `app/.env.local`:
```bash
cp app/.env app/.env.local
```

* Загрузить дамп базы из файла `docker/database/scripts/10-init.sql`.

* Запустить контейнеры:
```
docker-compose up --build
```

* Собрать вендоры:
```bash
sh scripts/vendors.sh
```

* Создать поисковой индекс:
```bash
sh scripts/elasticsearch.sh
```

* Доступен по ссылке `http://localhost:8080`

## Сборка ресурсов
```bash
npm install
yarn install
yarn encode run dev|prod
```
