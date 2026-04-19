# MedFlow Deployment Guide (Production)

This guide outlines the steps to deploy the **MedFlow** Healthcare platform to a production server (VPS, Cloud, or Managed Hosting).

---

## 1. Server Requirements
Ensure your server has the following installed:
- **PHP 8.2+** with extensions: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `curl`.
- **MySQL 8.0+** or MariaDB.
- **Nginx** or **Apache**.
- **Composer** (Dependency Manager).
- **Node.js & NPM** (for Asset Compilation).
- **Certbot** (for SSL/HTTPS).

---

## 2. Deployment Steps

### Step 1: Clone & Install Dependencies
Navigate to your web root (e.g., `/var/www/html`) and clone the repo:
```bash
git clone https://github.com/hameedshaik2345/Hospital-Management-System.git .
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### Step 2: Environment Configuration
Copy the `.env.example` to `.env` and update the production values:
```bash
cp .env.example .env
php artisan key:generate --force
```
**CRITICAL Production Settings in `.env`:**
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://yourdomain.com`
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (Prod credentials)
- `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`
- `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`

### Step 3: Database & Permissions
Run migrations and set directory permissions:
```bash
php artisan migrate --force --seed
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Step 4: Web Server Config (Nginx Example)
Point your web server's root to the `/public` directory of the project.
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Step 5: Optimization
Run these commands to cache your routes and config for faster performance:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 3. Important Production Notes

### 🔒 SSL (HTTPS) is MANDATORY
**Web Push Notifications** and **Geo-location** (Distance search) will **NOT work** over HTTP. You must use HTTPS. Use Let's Encrypt (Certbot) to secure your site:
```bash
sudo certbot --nginx -d yourdomain.com
```

### 🕒 Task Scheduling & Queues
To ensure notifications and background tasks run correctly, add a Cron job:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 📧 Push Notifications
Ensure your `VAPID` keys are correctly set in the production `.env`. If you move to a new domain, you may need to re-generate them to ensure compatibility across browsers.

---

## 🚀 Recommended Deployment Tools
- **Laravel Forge**: The easiest way to deploy and manage Laravel apps.
- **DigitalOcean / AWS / Linode**: Reliable VPS providers.
- **Cloudflare**: Use for extra security and CDN performance.
