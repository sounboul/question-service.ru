#!/bin/bash
# Выполнить миграцию БД
docker-compose exec -u www-data php-fpm bin/console doctrine:migrations:migrate
