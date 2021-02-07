#!/bin/bash
# Очистка кэша
docker-compose exec -u www-data php-fpm bin/console cache:clear
