# Healthcare Management System

A comprehensive web-based healthcare management system built with Laravel, designed for managing prenatal care, child records, and immunization tracking. This system is specifically designed for healthcare workers including midwives and Barangay Health Workers (BHW).

## Features

### 👩‍⚕️ For Midwives
- **Patient Management**: Complete patient registration and profile management
- **Prenatal Records**: Track pregnancy progress, medical history, and prenatal checkups
- **Child Records**: Comprehensive child health records with immunization history
- **Immunization Tracking**: Add, edit, and track vaccination records for children
- **Vaccine Management**: Inventory management for vaccines and supplies
- **Cloud Backup**: Secure data backup and restore functionality
- **User Management**: Create and manage BHW accounts
- **Reports**: Generate comprehensive healthcare reports

### 👩‍💼 For Barangay Health Workers (BHW)
- **Patient Registration**: Register new patients in the system
- **Prenatal Care**: Record and update prenatal checkups
- **Child Health Monitoring**: Track child development and immunizations
- **Basic Reporting**: Access to essential health reports

### 🔐 Authentication & Security
- Role-based access control (Midwife/BHW)
- Google OAuth integration for secure login
- Session management and user authentication

## Technology Stack

- **Backend**: Laravel 10.x (PHP Framework)
- **Frontend**: Blade Templates with Tailwind CSS
- **Database**: MySQL
- **Authentication**: Laravel Sanctum + Google OAuth
- **Icons**: Font Awesome
- **Development Server**: XAMPP

## Prerequisites

Before you begin, ensure you have the following installed on your system:

- **PHP**: >= 8.1
- **Composer**: Latest version
- **Node.js**: >= 16.x
- **MySQL**: >= 8.0
- **XAMPP**: Latest version (for local development)

## Installation

### 1. Clone the Repository

```bash
git clone <your-repository-url>
cd health-care
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Edit the `.env` file with your configuration:

```env
APP_NAME="Healthcare Management System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=health_care_db
DB_USERNAME=root
DB_PASSWORD=

# Google OAuth (Optional)
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/google/callback
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Database Setup

#### Create Database
Create a new MySQL database named `health_care_db` (or your preferred name as set in `.env`)

#### Run Migrations
```bash
php artisan migrate
```

#### Seed Database (Optional)
```bash
php artisan db:seed
```

### 7. Build Frontend Assets

For development:
```bash
npm run dev
```

For production:
```bash
npm run build
```

### 8. Create Storage Link

```bash
php artisan storage:link
```

## Running the Application

### Development Server

Start the Laravel development server:

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

### XAMPP Setup (Alternative)

1. Start Apache and MySQL in XAMPP Control Panel
2. Place the project folder in `C:\xampp\htdocs\`
3. Access the application at `http://localhost/health-care/public`

## Default Login Credentials

After running the seeders, you can use these default accounts:

### Midwife Account
- **Email**: midwife@example.com
- **Password**: password

### BHW Account
- **Email**: bhw@example.com
- **Password**: password

## Project Structure

```
health-care/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php
│   │   ├── ChildRecordController.php
│   │   ├── ChildImmunizationController.php
│   │   ├── PrenatalRecordController.php
│   │   └── ...
│   ├── Models/
│   │   ├── User.php
│   │   ├── Patient.php
│   │   ├── ChildRecord.php
│   │   ├── ChildImmunization.php
│   │   └── ...
│   └── ...
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── layout/
│   │   ├── midwife/
│   │   ├── bhw/
│   │   └── ...
│   ├── css/
│   └── js/
├── routes/
│   └── web.php
└── ...
```

## Key Dependencies

### Backend (Composer)

```json
{
    "require": {
        "php": "^8.1",
        "laravel/framework": "^10.10",
        "laravel/sanctum": "^3.2",
        "laravel/socialite": "^5.6",
        "google/apiclient": "^2.12",
        "guzzlehttp/guzzle": "^7.2"
    },
    "require-dev": {
        "laravel/sail": "^1.18",
        "mockery/mockery": "^1.4.4",
        "nunomaduro/collision": "^7.0",
        "phpunit/phpunit": "^10.1"
    }
}
```

### Frontend (NPM)

```json
{
    "devDependencies": {
        "axios": "^1.1.2",
        "laravel-vite-plugin": "^0.7.2",
        "vite": "^4.0.0",
        "tailwindcss": "^3.2.0",
        "autoprefixer": "^10.4.12",
        "postcss": "^8.4.31"
    }
}
```

## Database Schema

### Key Tables

- **users**: System users (midwives, BHWs)
- **patients**: Patient information
- **prenatal_records**: Prenatal care records
- **prenatal_checkups**: Checkup history
- **child_records**: Child health records
- **child_immunizations**: Vaccination records
- **vaccines**: Vaccine inventory

## Configuration

### Google OAuth Setup (Optional)

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable Google+ API
4. Create OAuth 2.0 credentials
5. Add authorized redirect URIs:
   - `http://localhost:8000/google/callback`
6. Update `.env` file with client ID and secret

### File Permissions

Ensure these directories are writable:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## Troubleshooting

### Common Issues

1. **"Class not found" errors**: Run `composer dump-autoload`
2. **Permission denied**: Check file permissions on storage and cache directories
3. **Database connection failed**: Verify database credentials in `.env`
4. **Assets not loading**: Run `npm run dev` or `npm run build`

### Clearing Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support or questions:
- Create an issue in the repository
- Contact the development team

## Version History

### v1.0.0
- Initial release
- Basic patient and prenatal record management
- User authentication and role-based access
- Child records with immunization tracking
- Google OAuth integration
- Cloud backup functionality

---

**Note**: This system is designed for healthcare management and should be used in compliance with local healthcare regulations and data privacy laws.