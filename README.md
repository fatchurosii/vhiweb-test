<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

---


1. **Clone the repository**
   ```bash
   https://github.com/fatchurosii/vhiweb-test.git
   cd vhiweb-test
   ```
2. **Install Depedency**
    ```
    composer install
    ```
3. **copy .env**
    ```
    cp .env.example .env
   ```
4. **Generate application key**
    ```
    php artisan key:generate
   ```
5. **Edit configuration database**
    ```
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=vhiweb_test
    DB_USERNAME=
    DB_PASSWORD=
   ```
6. **Run the database migration**
    ```
    php artisan migrate
   ```
7. **Seed user to database**
    ```
    php artisan db:seed --class=UserSeeder
   ```
8. **Import Collection to Postman**
    ```
    Import the Postman collection included in this project: `VhiWEB.json`
   ```
9 **Run Application**
    ```
    php artisan serve
   ```
Now the Laravel app can accessible on http://127.0.0.1:8000/
