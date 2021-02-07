#!/bin/bash
# Обновление поискового индекса
docker-compose exec -u www-data php-fpm bin/console app:elasticsearch:rebuild
