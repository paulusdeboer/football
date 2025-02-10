FROM wodby/php:8.3

COPY --chown=wodby:wodby . /var/www/html

RUN composer config -g cache-dir "$(pwd)/composer-cache"
RUN composer install --no-scripts --no-interaction --optimize-autoloader --prefer-dist -o

RUN cp .env.example .env
RUN php artisan key:generate

USER root

# XDebug cannot write to it's log
RUN touch /tmp/php-xdebug.log \
    && chown www-data:www-data /tmp/php-xdebug.log \
    && chmod 666 /tmp/php-xdebug.log

RUN touch ./storage/logs/laravel.log \
  && chown www-data:www-data ./storage/logs/laravel.log \
  && chmod -Rf 0777 ./storage/logs/laravel.log

USER wodby

RUN php artisan optimize

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["sudo", "-E", "LD_PRELOAD=/usr/lib/preloadable_libiconv.so", "php-fpm"]
