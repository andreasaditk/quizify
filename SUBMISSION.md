# 📱 Quizify - Project Submission untuk VPS Deployment

## 🔗 Repository Information
- **Repository URL**: https://github.com/andreasaditk/quizify
- **Branch**: main
- **Author**: andreasaditk
- **Framework**: Laravel 11
- **Status**: Ready for VPS Deployment ✅

## 📦 Technology Stack
- **Backend**: PHP 8.2 (Laravel 11)
- **Frontend**: Vue.js / Blade Templates
- **Database**: SQLite (development) / MySQL (production)
- **Package Manager**: Composer + NPM
- **Build Tool**: Vite

## 🚀 Quick Start untuk VPS Deployment

### Prerequisites
```
- PHP 8.2+ dengan extensions: pdo, pdo_mysql, bcmath, ctype, fileinfo, json, mbstring, openssl, tokenizer, xml
- Composer
- Node.js 18+ dan npm
- MySQL/MariaDB
- Nginx atau Apache
- Git
```

### Deployment Steps
1. **Clone Repository**
   ```bash
   git clone https://github.com/andreasaditk/quizify.git
   cd quizify
   ```

2. **Setup Environment**
   ```bash
   cp .env.example .env
   # Edit .env dengan database credentials
   nano .env
   ```

3. **Install Dependencies**
   ```bash
   composer install --optimize-autoloader --no-dev
   npm install
   npm run build
   ```

4. **Database Setup**
   ```bash
   php artisan key:generate
   php artisan migrate --force
   ```

5. **File Permissions**
   ```bash
   sudo chown -R www-data:www-data /var/www/quizify
   chmod -R 755 /var/www/quizify
   chmod -R 775 /var/www/quizify/storage
   chmod -R 775 /var/www/quizify/bootstrap/cache
   ```

## 📖 Dokumentasi Lengkap
Repository ini sudah include file **DEPLOYMENT.md** yang berisi:
- Konfigurasi .env untuk production
- Setup Nginx/Apache
- SSL dengan Let's Encrypt
- Cron jobs & Queue workers
- Backup & update procedures
- Troubleshooting guide

## 📁 Project Structure
```
quizify/
├── app/                  # Application logic
├── config/              # Configuration files
├── database/            # Migrations & seeders
├── public/              # Web root
├── resources/           # Views & assets
├── routes/              # API & web routes
├── storage/             # Logs & sessions
├── tests/               # Unit & feature tests
├── DEPLOYMENT.md        # Deployment guide (lengkap!)
└── README.md
```

## ✨ Features
- [x] Laravel 11 Framework
- [x] Database migrations ready
- [x] API routes configured
- [x] Asset compilation (Vite)
- [x] Session management
- [x] Logging configured
- [x] Testing setup (PHPUnit)

## 🔐 Security Notes
- **Jangan** commit .env ke repository ✅
- **.env.example** sudah tersedia untuk referensi
- .gitignore sudah configured dengan benar
- APP_DEBUG=false untuk production

## 💡 Database Configuration untuk VPS
Edit **.env** dengan credentials database:
```env
DB_CONNECTION=mysql
DB_HOST=localhost_atau_ip_database
DB_PORT=3306
DB_DATABASE=quizify
DB_USERNAME=quizify_user
DB_PASSWORD=your_strong_password_here
```

## 🆘 Support & Troubleshooting
Jika ada masalah saat deployment:
1. Check logs: `tail -f storage/logs/laravel.log`
2. Cek file permissions di storage & bootstrap/cache
3. Pastikan database connection sudah benar
4. Verify PHP extensions dengan `php -m`

---

**Repository siap untuk di-deploy ke VPS!** 🚀

Hubungi developer jika ada pertanyaan: andreasaditk@gmail.com
