#!/bin/bash

start=`date +%s`

echo '[BACKUP START]'
DATE_PREF=`date +%F`
BACKUP_PATH=/mnt/backup
BACKUP_DATABASE_DIR=$BACKUP_PATH\/database/$DATE_PREF
BACKUP_HTDOCS_DIR=$BACKUP_PATH\/htdocs/$DATE_PREF
BACKUP_UPLOADS_DIR=$BACKUP_PATH\/uploads/$DATE_PREF

echo '[BACKUP CLEANUP]'
find $BACKUP_PATH\/database/ -mtime +30 -print -mindepth 1 -delete >/dev/null 2>&1
find $BACKUP_PATH\/htdocs/ -mtime +30 -print -mindepth 1 -delete >/dev/null 2>&1
find $BACKUP_PATH\/uploads/ -mtime +15 -print -mindepth 1 -delete >/dev/null 2>&1

echo '[BACKUP HTDOCS]'
cd /var/www/fastuser/data/www/question-service.ru/
tar cpzf $BACKUP_HTDOCS_DIR\-htdocs.tgz --exclude=./var --exclude=./uploads -- . >/dev/null 2>&1

echo '[BACKUP MEDIA]'
cd /var/www/fastuser/data/www/question-service.ru/public/uploads
tar cpzf $BACKUP_UPLOADS_DIR\-.tgz -- . >/dev/null 2>&1

echo '[BACKUP DATABASE]'
pg_dump question_service | gzip > $BACKUP_DATABASE_DIR\-question_service_prod.gz

echo '[BACKUP END]'

end=`date +%s`
runtime=$((end-start))
echo 'Backup time = ' $runtime ' sec(s)'
echo '=============================================================='
