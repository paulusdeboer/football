docker compose -f docker-compose.yml -f docker-compose.dev.yml down
rm .env
cp .env.example .env
composer install --ignore-platform-reqs
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d
sleep 15
docker compose -f docker-compose.yml -f docker-compose.dev.yml exec php php artisan db:wipe
docker compose -f docker-compose.yml -f docker-compose.dev.yml exec php php artisan key:generate
docker compose -f docker-compose.yml -f docker-compose.dev.yml exec php php artisan migrate
docker compose -f docker-compose.yml -f docker-compose.dev.yml exec php php artisan storage:link || true
docker compose -f docker-compose.yml -f docker-compose.dev.yml exec php php artisan optimize:clear
