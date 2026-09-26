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

Members sign up with a username and/or phone plus a password. There is no SMS code — signup is protected by an on-page “I am not a robot” check.

Managers sign in with password, then approve the login from a registered browser. Withdrawals also need that device approval. Requests waiting for a decision are listed in the manager console under **Waiting for device approval**. “Keep me signed in” never skips withdrawal authorization.

Profile photos and product pictures are stored on disk (`storage/app/public` and `public/uploads`). The database keeps the image path/link (`avatar_path`, `image_path`). Minimum withdraw is 2,000 UGX. A 6% fee applies on each cash out. Members may cash out any day once available earnings cover the minimum.
