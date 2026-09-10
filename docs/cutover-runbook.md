# Plesk cutover and rollback runbook

## Before release

1. Confirm the Plesk Git branch, webhook/deployment command, PHP version, document root, storage link, environment variables and scheduled tasks.
2. Create a full database backup and retain the current application release for rollback.
3. Build and test the release against a copy of the production database.
4. Confirm there is no active game and no rating request link that must remain usable.

The read-only parity suite is opt-in and must be run against the imported copy from
inside Docker, for example:

`docker compose exec -e RUN_PRODUCTION_PARITY=1 -e PARITY_DB_CONNECTION=mariadb php php artisan test --filter=ProductionParityTest`

Do not enable `RUN_PRODUCTION_PARITY` for the normal test suite: normal PHPUnit tests
use an isolated SQLite in-memory database.

## Release

1. Announce the maintenance window and stop new writes to the old release.
2. Take a final database backup.
3. Deploy the new branch through Plesk.
4. Run only additive migrations, cache rebuilds and asset builds; never run `migrate:fresh`, `db:wipe`, seeders or `key:generate`.
5. Run `php artisan migrate --force`. The additive migrations assign the `admin` role automatically to Sjoerd Koffeman (`skoffeman@live.nl`) and Paulus de Boer (`paulusdeboer8@outlook.com`), matching both name and email because duplicate email addresses are allowed. The fallback command accepts the same two values: `php artisan users:bootstrap-admin "Sjoerd Koffeman" skoffeman@live.nl`.
6. Refresh Laravel's caches **after** the new files and migrations are in place. Run `php artisan optimize:clear`, then `php artisan config:cache`, `php artisan route:cache` and `php artisan view:cache`. The route-cache step is required for new pages such as `/settings/whatsapp`; otherwise the frontend link can be present while the production server still returns 404 from an older cached route collection.
7. Verify document root, permissions, storage and Vite build output.
8. Run admin login, player-role restriction, player, game, result, rating, WhatsApp settings and mail smoke tests. Confirm `php artisan route:list --path=settings/whatsapp` shows the WhatsApp routes before opening the page.

## Rollback

- If only code is faulty, restore the previous release and keep the database unchanged.
- If a new write cannot be safely reversed, restore the final pre-release backup after stopping traffic.
- Record the failure, affected request and database state before retrying.
