# Namecheap Shared Hosting Deployment Guide

## Architecture (Safe — other sites unaffected)

```
/home/yourusername/
├── laravel/
│   └── store-pos/          ← Clone repo here (NOT inside public_html)
│       ├── app/
│       ├── bootstrap/
│       ├── config/
│       ├── database/
│       ├── routes/
│       ├── storage/
│       ├── vendor/
│       ├── .env
│       └── public/         ← Do NOT expose this directly
│
└── public_html/
    └── store-pos/          ← Web-accessible folder only
        ├── index.php       ← Copy from public/ and update path
        ├── .htaccess       ← Copy from public/
        ├── robots.txt      ← Copy from public/
        └── build/          ← Copy entire build/ folder from public/build/
```

---

## Step-by-Step Deployment

### 1. Set PHP version to 8.2
In cPanel → MultiPHP Manager → Set PHP 8.2 for your domain.

### 2. Clone the repo outside public_html (via SSH or cPanel Terminal)
```bash
mkdir -p ~/laravel
cd ~/laravel
git clone https://github.com/srahmancs-cyber/store-pos.git store-pos
```

### 3. Install dependencies
```bash
cd ~/laravel/store-pos
composer install --no-dev --optimize-autoloader
```

### 4. Set up .env
```bash
cp .env.production.example .env
nano .env   # Fill in DB credentials, APP_URL, etc.
```
Then generate the app key:
```bash
php artisan key:generate
```

### 5. Create the MySQL database
In cPanel → MySQL Databases:
- Create database: `yourusername_storepos`
- Create user, assign all privileges
- Update .env with these credentials

### 6. Run migrations
```bash
php artisan migrate --seed
```

### 7. Create the web-accessible folder
```bash
mkdir -p ~/public_html/store-pos
```

### 8. Copy public folder contents to web folder
```bash
cp ~/laravel/store-pos/public/index.php ~/public_html/store-pos/
cp ~/laravel/store-pos/public/.htaccess ~/public_html/store-pos/
cp ~/laravel/store-pos/public/robots.txt ~/public_html/store-pos/
cp -r ~/laravel/store-pos/public/build ~/public_html/store-pos/
```

### 9. Update index.php to point to Laravel root
Edit `~/public_html/store-pos/index.php`:
```php
// Change this line:
$laravelRoot = __DIR__ . '/../';

// To your actual path:
$laravelRoot = '/home/yourusername/laravel/store-pos/';
```

### 10. Fix storage permissions
```bash
chmod -R 775 ~/laravel/store-pos/storage
chmod -R 775 ~/laravel/store-pos/bootstrap/cache
```

### 11. Create storage symlink (for uploaded files)
```bash
php ~/laravel/store-pos/artisan storage:link --relative
# This creates: ~/laravel/store-pos/public/storage → ../storage/app/public
# Then copy the symlink target to web folder:
cp -r ~/laravel/store-pos/public/storage ~/public_html/store-pos/
```

### 12. Set up cron job
In cPanel → Cron Jobs, add:
```
* * * * * php /home/yourusername/laravel/store-pos/artisan schedule:run >> /dev/null 2>&1
```

### 13. Optimize for production
```bash
cd ~/laravel/store-pos
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Updating after code changes

```bash
cd ~/laravel/store-pos
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Re-copy public assets if they changed:
cp -r public/build ~/../../public_html/store-pos/
```

---

## Access the site
`https://yourdomain.com/store-pos`

Default login:
- Email: `admin@store.com`
- Password: `password` (change immediately after first login)
