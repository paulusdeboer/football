#!/usr/bin/env bash
set -x

DBEXISTS=$(mysql -u root -p${ROOT_PASSWORD} -h mariadb --batch --skip-column-names -e "SHOW DATABASES LIKE 'migrate';" | grep migrate > /dev/null; echo "$?")
if [ $DBEXISTS -eq 1 ];then
    mysql -u root -p${ROOT_PASSWORD} -h mariadb -e "CREATE DATABASE migrate;"
    mysql -u root -p${ROOT_PASSWORD} -h mariadb -e "GRANT ALL PRIVILEGES ON migrate.* TO 'laravel'@'%';"
    mysql -u root -p${ROOT_PASSWORD} -h mariadb migrate < database/dumps/migrate.sql
fi

php artisan seed:from-old-db migrate
