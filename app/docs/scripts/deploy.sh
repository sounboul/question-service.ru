### === PROJECT DEPLOY ===
# settings
PROJECT="/var/www/fastuser/data/www/question-service.ru/"

# git pull
cd $PROJECT;
git pull --ff-only

cd $PROJECT/app;

# database migration
bin/console doctrine:migrations:migrate

# composer (весь кэш будет удален скриптами из composer)
composer install --no-dev --optimize-autoloader
composer dump-autoload --no-dev --classmap-authoritative
