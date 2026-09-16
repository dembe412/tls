# TSL Smart Locks (Laravel)

Mobile-first shop for TSL Tuya smart locks. Guests browse available locks; tapping a lock asks them to sign up first.

## Run locally

```sh
cd laravel
composer install
php artisan migrate --seed
php artisan serve
```

Open http://localhost:8000

- **Home** — locks for purchase
- **Products** — full catalogue
- **News** — TSL updates
- **My account** — signup / owner card

The first account created becomes the manager. Later accounts are clients.

Phone + password login. After a client requests a lock, the manager marks it as bought so the daily interest / 35-day cycle starts. The manager enters the daily interest amount for each lock.
