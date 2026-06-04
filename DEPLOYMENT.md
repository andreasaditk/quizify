# Panduan Deployment Quizify ke VPS

## 📋 Prerequisites di VPS
- PHP 8.2+ dengan extensions: pdo, pdo_mysql, pdo_sqlite, bcmath, ctype, fileinfo, json, mbstring, openssl, pdo, pdlib, tokenizer, xml
- Composer
- Node.js 18+ dan npm
- MySQL/MariaDB (untuk production)
- Git
- Web server: Nginx atau Apache
- SSL certificate (Let's Encrypt)

## 🚀 Langkah-langkah Deployment

### 1. SSH ke VPS
```bash
ssh user@your_vps_ip
```

### 2. Navigasi ke direktori public web
```bash
cd /var/www
# atau sesuai konfigurasi VPS dosen Anda
```

### 3. Clone Repository
```bash
git clone https://github.com/YOUR_USERNAME/quizify.git
cd quizify
```

### 4. Setup Environment Variables
```bash
# Copy file example
cp .env.example .env

# Edit konfigurasi untuk production
nano .env
```

**Konfigurasi penting di .env:**
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:xxxxx  # Generate dengan: php artisan key:generate

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost  # atau IP database
DB_PORT=3306
DB_DATABASE=quizify
DB_USERNAME=quizify_user
DB_PASSWORD=your_strong_password

# URL
APP_URL=https://yourdomain.com

# Mail (opsional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465

# Session
SESSION_DRIVER=database

# Cache & Queue
CACHE_STORE=file
QUEUE_CONNECTION=database
```

### 5. Install Dependencies
```bash
# PHP dependencies
composer install --optimize-autoloader --no-dev

# Node dependencies & build assets
npm install
npm run build
```

### 6. Database Setup
```bash
# Generate app key
php artisan key:generate

# Migrate database
php artisan migrate --force

# (Optional) Seed database
php artisan db:seed
```

### 7. Setup File Permissions
```bash
# Set directory ownership
sudo chown -R www-data:www-data /var/www/quizify

# Set permissions
chmod -R 755 /var/www/quizify
chmod -R 775 /var/www/quizify/storage
chmod -R 775 /var/www/quizify/bootstrap/cache

# Set special permissions for storage
sudo chown -R www-data:www-data /var/www/quizify/storage
sudo chown -R www-data:www-data /var/www/quizify/bootstrap/cache
```

### 8. Setup Web Server

#### Nginx Configuration
Create file: `/etc/nginx/sites-available/quizify`
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/quizify/public;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/quizify /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 9. Setup SSL (Let's Encrypt)
```bash
sudo apt-get install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### 10. Setup Cron Job untuk Scheduler
```bash
# Edit crontab
crontab -e

# Tambahkan:
* * * * * cd /var/www/quizify && php artisan schedule:run >> /dev/null 2>&1
```

### 11. Setup Queue Worker (jika menggunakan queue)
```bash
# Buat service file untuk queue worker
sudo nano /etc/systemd/system/quizify-queue.service
```

```ini
[Unit]
Description=Quizify Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/quizify
ExecStart=/usr/bin/php /var/www/quizify/artisan queue:work
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable quizify-queue
sudo systemctl start quizify-queue
```

## 🔄 Update & Maintenance

### Update aplikasi
```bash
cd /var/www/quizify
git pull origin main
composer install --optimize-autoloader --no-dev
npm install
npm run build
php artisan migrate --force
php artisan cache:clear
php artisan config:clear
```

### Backup Database
```bash
mysqldump -u quizify_user -p quizify > backup_$(date +%Y%m%d_%H%M%S).sql
```

## 🐛 Troubleshooting

### Permission Denied
```bash
sudo chown -R www-data:www-data /var/www/quizify
chmod -R 775 /var/www/quizify/storage
```

### 500 Error
Check logs:
```bash
tail -f /var/www/quizify/storage/logs/laravel.log
```

### Database Connection Error
Pastikan credentials di .env benar dan database sudah dibuat:
```bash
mysql -u root -p
CREATE DATABASE quizify;
CREATE USER 'quizify_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON quizify.* TO 'quizify_user'@'localhost';
FLUSH PRIVILEGES;
```

## 📞 Support
Jika ada masalah, hubungi hosting support atau dosen Anda!
