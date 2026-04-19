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

## 🚀 Deployment to Vercel (Recommended Free Option)
Vercel is great for the frontend & logic, but requires a Cloud Database (not SQLite).

### 1. Database Setup (Neon.tech)
1. Go to [Neon.tech](https://neon.tech/) and create a free project.
2. Under "Connection Details", copy the **Postgres Connection String**. It will look like: 
   `postgresql://owner:pass@host/neondb?sslmode=require`

### 2. Vercel Setup
1. Import your repository to Vercel.
2. In the **Environment Variables** section, add:
   - `DB_CONNECTION`: `pgsql`
   - `DB_URL`: (Paste your Neon connection string)
   - `APP_KEY`: (Copy from your local .env)
   - `APP_URL`: `https://your-app-name.vercel.app`
   - `APP_ENV`: `production`
   - `APP_DEBUG`: `false`
   - `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`
   - `FAST2SMS_API_KEY`, `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`

### 3. Database Migration
Since Vercel is serverless, you need to run migrations manually from your local machine once pointing to the Neon DB.

**Local Migration to Cloud:**
1. Temporarily paste your Neon `DB_URL` into your local `.env`.
2. Run: `php artisan migrate --force --seed`
3. Remember to revert your local `.env` back to SQLite afterwards.

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
