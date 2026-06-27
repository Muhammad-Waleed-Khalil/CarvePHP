# Installation

## Requirements

- PHP 8.2+
- Laravel 10, 11, or 12
- Composer

## Install

```bash
composer require carvephp/carve --dev
php artisan carve:install
php artisan migrate
```

The install command publishes `config/carve.php` and the database migrations.

## Verify

```bash
php artisan carve:doctor
```

This checks PHP version, Laravel version, config status, database connection, and discovers available routes.
