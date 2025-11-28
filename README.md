# DialerPad

Public QR dialer + admin portal for managing call cards and eSIM requests.

## Stack
- Laravel 10, PHP 8.2+
- MySQL/MariaDB
- Tailwind (public pages) + Bootstrap (admin)

## Setup
1) Install dependencies  
```bash
composer install
npm install
```

2) Copy env + key  
```bash
cp .env.example .env
php artisan key:generate
```

3) Configure `.env` (DB, APP_URL, mail).  

4) Build assets (for local dev)  
```bash
npm run dev
```

5) Migrate + seed  
```bash
php artisan migrate --seed
php artisan storage:link
```

Default admin: `admin@example.com` / `password` (change after first login).

## Core flows
- **Call Cards & QR**: Create cards in `/admin/call-cards` → download QR (encodes `/c/{uuid}`). Scanning shows the public dialer. Calls create `CallSession` rows; minutes decrement and cards auto-expire when usage hits the limit.
- **Public Dialer**: `/c/{uuid}` shows keypad with prefix + timer. Start/End calls hit `/start-call` and `/end-call` endpoints; UI updates remaining minutes and blocks expired cards. (No real telephony yet.)
- **eSIM Activation (UI-only)**: `/esim/activate` collects requests (name, email, phone, device, selected eSIM type). Admin can manage types and requests in `/admin/esim-types` and `/admin/esim-requests`. API to Mobimatter/FreePBX is intentionally not wired yet.

## Notes & limitations
- Telephony is simulated: endpoints create sessions and decrement minutes but do not place real calls.
- eSIM API integration is pending; requests are stored for manual processing.
- QR generation writes to `storage/app/public/qrcodes`; ensure `storage:link` and the `public` disk are writable.

## Deploy
- See `DEPLOYMENT.md` for a Vultr-ready checklist (PHP extensions, commands, assets, and cron queue/cleanup if needed).

## Demo checklist (for Cheick)
1) Create 1–2 call cards (e.g., “Nigeria 100 min”, “France 50 min”) and export QR ZIP.  
2) Scan/open `/c/{uuid}` → dial a test number → start/end calls (different durations).  
3) Show minutes decreasing and card expiring at the limit; confirm sessions appear in `/admin/call-sessions`.  
4) Show `/esim/activate`, submit a request, and review it in `/admin/esim-requests` (mention “API hookup will be Phase 2”).  
