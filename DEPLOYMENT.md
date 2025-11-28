# Deployment (Vultr / Ubuntu)

## Server prep
- PHP 8.2+ with extensions: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `imagick`.
- Composer installed globally.
- Node 18+ only if building assets on the server (otherwise deploy built `public/build`).
- MySQL/MariaDB database created with user/grants.

## App steps
1) Clone repo + set working dir.  
2) Copy env and set values: `cp .env.example .env` then fill `APP_URL`, DB, mail, queue, filesystem.  
3) Install deps: `composer install --no-dev` (and `npm ci && npm run build` if building assets).  
4) Generate key: `php artisan key:generate`.  
5) Migrate/seed: `php artisan migrate --force --seed`.  
6) Storage symlink: `php artisan storage:link`.  
7) Cache optimizations (optional):  
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Processes
- Queue: `php artisan queue:work --daemon` (or supervisor) if you switch queue usage from sync to database.  
- Scheduler (optional future jobs): cron `* * * * * php /path/artisan schedule:run >> /dev/null 2>&1`.

## Notes
- QR codes save to `storage/app/public/qrcodes`; ensure `public/storage` is writable and web-accessible.  
- Telephony/eSIM APIs are not connected yet; the public dialer and eSIM form are demo/collection only.  
- If deploying built assets only, upload `public/build` from `npm run build` and disable Node on the server.  
