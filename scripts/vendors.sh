#!/bin/bash
# Обновление вендоров
docker-compose exec -u www-data php-fpm composer install --optimize-autoloader --no-interaction
docker-compose exec -u www-data php-fpm composer dump-autoload --optimize --classmap-authoritative
