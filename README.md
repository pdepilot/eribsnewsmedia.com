# ERIBS Media

Laravel 13 newsroom for ERIBS Media. Public pages use the magazine layout taken from the saved homepage. Stories, categories, ads, and breaking news come from the database.

See `MIGRATION.md` for what was in the saved site and what was not imported.

## Run locally

```bash
composer install
npm install
npm run build
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Local newsroom login (not created when `APP_ENV=production`):

- `admin@eribs.test`
- `password`

The default database is SQLite. Production should use MySQL or MariaDB and must set `APP_ENV=production` and `APP_DEBUG=false`.

Schedule `php artisan schedule:run` every minute so scheduled articles publish.
