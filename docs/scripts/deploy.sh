### === PROJECT DEPLOY ===
# settings
PROJECT="/var/www/fastuser/data/www/question-service.ru/"

# git pull
cd $PROJECT;
git pull --ff-only

# composer (весь кэш будет удален скриптами из composer)
composer install --no-dev --optimize-autoloader
composer dump-autoload --no-dev --classmap-authoritative
