#!/bin/bash
# Выливка кода
PROJECT="/home/fastuser/app"
cd $PROJECT

# GIT PULL
git reset --hard
git pull --ff-only

# Выполнение миграциий
sh $PROJECT/scripts/migration.sh

# Очистка кэша
sh $PROJECT/scripts/clearcache.sh
