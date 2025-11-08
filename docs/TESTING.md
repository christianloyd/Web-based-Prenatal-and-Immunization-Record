# Testing Guide

## Quick Setup

This project uses PHPUnit for testing. Due to system limitations, tests require either SQLite or MySQL PDO extension.

### Current Status

- **Issue**: Tests fail with "could not find driver" error
- **Cause**: PHP SQLite PDO extension (`pdo_sqlite`) is not installed
- **Quick Fix Applied**: Tests now skip gracefully when database is unavailable

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

### Setting Up Database for Tests

#### Option 1: Install SQLite Extension (Recommended)

```bash
# Ubuntu/Debian
sudo apt-get install php-sqlite3

# Or for specific PHP version
sudo apt-get install php8.4-sqlite3
```

#### Option 2: Use MySQL for Testing

1. Start MySQL service:
```bash
sudo service mysql start
```

2. Create testing database:
```bash
mysql -u root -e "CREATE DATABASE testing;"
```

3. Update `phpunit.xml`:
```xml
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_DATABASE" value="testing"/>
```

### Test Categories

- **Unit Tests** (`tests/Unit/`): Test individual classes and methods
  - ImmunizationServiceTest
  - NotificationServiceTest

- **Feature Tests** (`tests/Feature/`): Test complete features and workflows
  - PatientRegistrationTest
  - PatientApiTest

### Known Issues

1. **PHPUnit Deprecation Warnings**: Tests use deprecated doc-comment annotations (`@test`). These should be migrated to PHP attributes for PHPUnit 12 compatibility.

2. **Database Driver**: Without SQLite or MySQL configured, all database-dependent tests will be skipped.

### Troubleshooting

**Tests are being skipped**:
- Database is not configured or unavailable
- Install SQLite extension or configure MySQL (see above)

**Connection refused errors**:
- MySQL service is not running
- Start MySQL: `sudo service mysql start`

**Migration errors**:
- Database doesn't exist
- Create it: `mysql -u root -e "CREATE DATABASE testing;"`
