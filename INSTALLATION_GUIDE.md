 Healthcare Management System - Installation Guide

 Overview
This guide provides step-by-step instructions for installing and setting up the Healthcare Management System on your local development environment or production server.

 System Requirements

 Minimum Requirements
- PHP: 8.2 or higher
- MySQL: 8.0 or higher
- Web Server: Apache 2.4+ or Nginx 1.18+
- Memory: 512 MB RAM minimum (1 GB recommended)
- Storage: 2 GB free disk space

 Required PHP Extensions
- BCMath PHP Extension
- Ctype PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- cURL PHP Extension
- GD PHP Extension (for image processing)
- ZIP PHP Extension (for backups)

 Development Tools
- Composer: Latest version
- Node.js: 18.x or higher
- NPM: Latest version

---

 Installation Methods

 Method 1: XAMPP (Recommended for Local Development)

 Step 1: Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP with PHP 8.2 or higher
3. Start Apache and MySQL services

 Step 2: Clone the Project
```bash
 Navigate to htdocs directory
cd C:\xampp\htdocs

 Clone or copy the project
git clone <your-repository-url> health-care
 OR copy the project folder to htdocs/health-care
```

 Step 3: Install Dependencies
```bash
 Navigate to project directory
cd health-care

 Install PHP dependencies
composer install

 Install Node.js dependencies
npm install
```

 Step 4: Environment Configuration
```bash
 Copy environment file
copy .env.example .env
```

Edit the `.env` file:
```env
APP_NAME="Healthcare Management System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost/health-care/public

 Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=health_care_db
DB_USERNAME=root
DB_PASSWORD=

 Google OAuth (Optional)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost/health-care/public/google/callback
```

 Step 5: Generate Application Key
```bash
php artisan key:generate
```

 Step 6: Database Setup
1. Open phpMyAdmin (`http://localhost/phpmyadmin`)
2. Create a new database named `health_care_db`
3. Run migrations:
```bash
php artisan migrate
```

 Step 7: Seed Database (Optional)
```bash
php artisan db:seed
```

 Step 8: Build Assets
```bash
npm run build
```

 Step 9: Create Storage Link
```bash
php artisan storage:link
```

 Step 10: Access the Application
Open your browser and navigate to:
`http://localhost/health-care/public`

---

 Method 2: Laravel Development Server

 Prerequisites
- PHP 8.2+ installed with required extensions
- Composer installed
- MySQL server running

 Step 1: Clone and Setup
```bash
 Clone the project
git clone <your-repository-url> health-care
cd health-care

 Install dependencies
composer install
npm install
```

 Step 2: Environment Configuration
```bash
 Copy environment file
cp .env.example .env

 Generate application key
php artisan key:generate
```

Edit `.env` file:
```env
APP_NAME="Healthcare Management System"
APP_ENV=local
APP_KEY=base64:generated_key_here
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=health_care_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

 Step 3: Database Setup
```bash
 Create database (using MySQL command line or GUI)
mysql -u root -p
CREATE DATABASE health_care_db;
exit

 Run migrations
php artisan migrate

 Seed database (optional)
php artisan db:seed
```

 Step 4: Build Assets and Start Server
```bash
 Build frontend assets
npm run build

 Create storage link
php artisan storage:link

 Start development server
php artisan serve
```

Access at: `http://localhost:8000`

---

 Advanced Configuration

 Google OAuth Setup

 Step 1: Create Google Cloud Project
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable Google+ API

 Step 2: Create OAuth Credentials
1. Go to "Credentials" in the API Console
2. Click "Create Credentials" > "OAuth 2.0 Client IDs"
3. Configure the consent screen
4. Add authorized redirect URIs:
   - `http://localhost:8000/google/callback` (development)
   - `https://yourdomain.com/google/callback` (production)

 Step 3: Update Environment
Add to `.env`:
```env
GOOGLE_CLIENT_ID=your_client_id_here
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost:8000/google/callback
```

 Email Configuration (Optional)

For email notifications, configure mail settings in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

 Queue Configuration (Optional)

For background jobs and notifications:
```env
QUEUE_CONNECTION=database
```

Run queue worker:
```bash
php artisan queue:work
```

---

 Production Deployment

 Server Requirements
- Web Server: Apache or Nginx with proper configuration
- SSL Certificate: For HTTPS (recommended)
- Domain: Properly configured domain name
- Backup Solution: Regular database and file backups

 Apache Virtual Host Configuration
```apache
<VirtualHost :80>
    ServerName yourdomain.com
    DocumentRoot /var/www/health-care/public

    <Directory /var/www/health-care/public>
        AllowOverride All
        Order allow,deny
        Allow from all
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/health-care_error.log
    CustomLog ${APACHE_LOG_DIR}/health-care_access.log combined
</VirtualHost>
```

 Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/health-care/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

 Production Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

 Use strong database credentials
DB_PASSWORD=strong_secure_password

 Configure session and cache
SESSION_DRIVER=database
CACHE_DRIVER=file

 Enable logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

 Security Considerations
1. File Permissions:
   ```bash
   chmod -R 755 /var/www/health-care
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```

2. Hide Sensitive Files:
   - Ensure `.env` is not accessible via web
   - Configure web server to block access to sensitive directories

3. SSL Certificate:
   - Install SSL certificate for HTTPS
   - Redirect HTTP to HTTPS

4. Database Security:
   - Use strong passwords
   - Restrict database access
   - Regular security updates

---

 Default User Accounts

After running the database seeder, you can use these accounts:

 Midwife Account
- Username: `admin`
- Password: `password123`
- Role: Midwife (Full Access)

 BHW Account
- Username: `bhw_user`
- Password: `password123`
- Role: BHW (Limited Access)

Important: Change these default passwords immediately after installation!

---

 Troubleshooting

 Common Issues

 1. Composer Installation Fails
```bash
 Clear composer cache
composer clear-cache

 Update composer
composer self-update

 Install with verbose output
composer install -v
```

 2. Database Connection Errors
- Verify database credentials in `.env`
- Ensure MySQL service is running
- Check database exists
- Test connection manually

 3. Permission Errors
```bash
 Fix storage permissions (Linux/Mac)
chmod -R 775 storage
chmod -R 775 bootstrap/cache

 Set proper ownership
chown -R www-data:www-data /var/www/health-care
```

 4. Asset Compilation Issues
```bash
 Clear Node modules and reinstall
rm -rf node_modules
rm package-lock.json
npm install

 Force rebuild
npm run build
```

 5. Key Generation Issues
```bash
 Manual key generation
php artisan key:generate --force

 Clear configuration cache
php artisan config:clear
```

 Log Files Location
- Application Logs: `storage/logs/laravel.log`
- Web Server Logs: Check your web server configuration
- Database Logs: MySQL error log location varies by installation

 Performance Optimization
```bash
 Cache configuration
php artisan config:cache

 Cache routes
php artisan route:cache

 Cache views
php artisan view:cache

 Optimize composer autoloader
composer install --optimize-autoloader --no-dev
```

---

 Maintenance Commands

 Regular Maintenance
```bash
 Clear all caches
php artisan optimize:clear

 Update dependencies
composer update
npm update

 Run migrations
php artisan migrate

 Generate fresh caches
php artisan optimize
```

 Database Maintenance
```bash
 Backup database
mysqldump -u username -p health_care_db > backup.sql

 Restore database
mysql -u username -p health_care_db < backup.sql
```

---

 Support and Resources

 Getting Help
- Check the `README.md` file for basic information
- Review error logs for specific issues
- Verify system requirements are met
- Ensure all environment variables are properly set

 Documentation Files
- `README.md` - Basic project information
- `DATABASE_SCHEMA.md` - Database structure
- `API_ROUTES.md` - API endpoints
- `USER_ROLES_PERMISSIONS.md` - User access control
- `SMS_INTEGRATION_OVERVIEW.md` - SMS feature planning

 Version Control
- Keep track of customizations
- Regular commits to version control
- Maintain separate branches for development and production
- Document any custom modifications

This installation guide should help you get the Healthcare Management System up and running in your environment. Follow the appropriate method based on your setup requirements and environment.