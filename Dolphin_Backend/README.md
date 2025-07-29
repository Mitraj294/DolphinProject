# Dolphin Backend (Laravel)

This is the backend API for the Dolphin project, built with Laravel.

## Features
- RESTful API for assessment, lead, user management
- Authentication (Passport/Sanctum)
- SQLite database (default for development)
- Modular structure: Controllers, Models, Providers
- API routes in `routes/api.php`

## Setup
1. Install dependencies:
   ```bash
   composer install
   ```
2. Copy `.env.example` to `.env` and configure your environment variables.
3. Run migrations:
   ```bash
   php artisan migrate
   ```
4. Start the server:
   ```bash
   php artisan serve --host=127.0.0.1
   ```

## Testing
Run tests with:
```bash
php artisan test
```

## API Documentation
Document your endpoints in this file or use Swagger/OpenAPI for better collaboration.

## Folder Structure
- `app/Http/Controllers`: API controllers
- `app/Models`: Eloquent models
- `routes/api.php`: API routes
- `database/migrations`: DB migrations
- `tests/`: Unit and feature tests

## License
MIT
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).



# Start Laravel backend
(lsof -ti:8000 | xargs -r kill)
(cd Dolphin_backend && php artisan serve --host=127.0.0.1 --port=8000 &)