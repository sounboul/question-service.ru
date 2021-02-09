#!/bin/bash
# Выливка кода
PROJECT="/home/fastuser/app"
cd $PROJECT

# docker-compose down
docker-compose down

# GIT PULL
git reset --hard
git pull --ff-only
git reset --hard origin/main

# Очистка кэша и логов
rm -rf $PROJECT/app/var/*

# docker-compose up
docker-compose up -d --build --force-recreate

# composer dump
docker-compose exec -u www-data php-fpm composer dump-autoload --optimize --classmap-authoritative
