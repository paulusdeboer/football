#!/usr/bin/env bash
set -x

DBEXISTS=$(mysql -u root -p${ROOT_PASSWORD} -h mariadb --batch --skip-column-names -e "SHOW DATABASES LIKE 'migrate';" | grep migrate > /dev/null; echo "$?")
if [ $DBEXISTS -eq 1 ];then
    mysql -u root -p${ROOT_PASSWORD} -h mariadb -e "CREATE DATABASE migrate; GRANT ALL PRIVILEGES ON migrate.* TO '${DB_USERNAME}'@'%';"
fi

mysql -u root -p${ROOT_PASSWORD} -h mariadb migrate < database/dumps/migrate.sql

php artisan seed:from-old-db migrate
