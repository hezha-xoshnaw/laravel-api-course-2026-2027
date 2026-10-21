# Backend API Course

A Laravel 13 application for learning backend development and REST APIs.

This is the Week 4 starting version. Its application code is the completed Week 3 version: tickets are stored in SQLite, and the API lists, shows, and creates them. Update and delete will be implemented during the lesson.

- [Week 4 guide](lessons/week-04/GUIDE.md) — partial updates, validation, deletion, and response status codes.
- [Week 4 command reference](lessons/week-04/QUICK-REFERENCE.md) — commands, patterns, and Postman checks.

- [Week 3 guide](lessons/week-03/GUIDE.md) — original steps to follow from the start version.
- [Week 3 completed changes](lessons/week-03/CHANGES.md) — what changed, Postman checks, and preparation for the next lesson.
- [Week 3 command reference](lessons/week-03/QUICK-REFERENCE.md) — commands and Eloquent patterns.
- [Week 2 recap](lessons/week-02/CHANGES.md) — controllers, validation, and Resources with static arrays.
- [Week 1 recap](lessons/week-01/CHANGES.md) — API preparation and the hello route.

## Requirements

- PHP 8.3 or newer, with the extensions required by the installed packages.
- Composer 2.
- Git, when cloning the course repository.
- VS Code or another code editor.
- Postman for the API lessons.

This version retains SQLite, Laravel's default database. Enable `pdo_sqlite` and `sqlite3` in PHP. MySQL will be configured in a later lesson.

## Get the project

To get this starting version:

```sh
git clone --branch week-04-start https://github.com/hezha-xoshnaw/laravel-api-course-2026-2027.git course-api
cd course-api
```

This command works once the lecturer publishes `week-04-start`. If you received a folder instead, open a terminal inside it. You should see the `artisan` and `composer.json` files.

## First-time setup

Run these commands in the project folder:

```sh
php -v
composer install
composer check-platform-reqs
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
php artisan db:seed
php artisan serve
```

Open **http://127.0.0.1:8000/api/hello**. You should see:

```json
{"message":"Hello World"}
```

The root URL `/` returns 404 because the welcome-page route was removed.

- `composer install` downloads the exact package versions in `composer.lock`.
- `.env` holds settings for your local copy.
- `key:generate` creates the application's encryption key. Run it during initial setup, not every time you start the project.
- `migrate` applies pending migrations, including the tickets table.
- `db:seed` adds two sample tickets. Rerunning it matches these sample titles and resets their statuses.
- `serve` starts the local development server. Keep its terminal open; press **Ctrl+C** to stop it.

No frontend packages or NPM build are needed. Use `php artisan serve` to start the application; `composer run dev` is also configured to start this server. Ticket endpoints now use the SQLite database. The hello endpoint does not query it.

Next, try `GET /api/tickets`. See the [Week 3 checks](lessons/week-03/CHANGES.md#run-and-check) for the other endpoints and POST input.

## Run an existing copy

If upgrading an existing Week 2 copy, run `php artisan migrate` and `php artisan db:seed` once. There is no need to regenerate the application key.

After setup, start the application with:

```sh
php artisan serve
```

Use a second terminal for other commands:

```sh
php artisan route:list
```

If port 8000 is busy, run `php artisan serve --port=8001` and open **http://127.0.0.1:8001**.

## Before coming to an offline class

1. Run the first-time setup while internet is available.
2. Confirm that `/api/hello` returns JSON and check the ticket endpoints in Postman using the Week 3 completed changes.
3. Bring the complete project folder, including `vendor/`, `.env`, and `database/database.sqlite`.
4. After copying to the classroom PC, run `composer check-platform-reqs`, then `php artisan serve`.

Cloning source code alone does not include downloaded dependencies. Keep `vendor/` in your offline copy and do not run `composer update` during class.

Your local `.env`, database, and `vendor/` are not intended for Git. Keep `composer.lock` with the source so everyone installs the same versions.

## Windows / XAMPP

Run `php -v` to verify that the terminal is using PHP 8.3 or newer. In Command Prompt, `where php` shows which executable is used.

If PHP is installed at `C:\xampp\php\php.exe` but is not on PATH, add `C:\xampp\php` to PATH and reopen the terminal. Use `php --ini` to find the active configuration file if an extension is missing.

For this SQLite baseline, neither Apache nor XAMPP's MySQL service needs to run. `php artisan serve` provides the local web server.

## Weekly versions

The course uses these branch names:

- `main`: the fresh starting application and setup instructions.
- `week-01-start`, `week-02-start`, and so on: the starting code and guide for a lesson.
- `week-01-end`, `week-02-end`, and so on: the completed lesson and a summary of changes.

Once the relevant branch is published, select the version named by your lecturer, for example:

```sh
git switch week-01-start
```

Save your work before switching branches; do not discard unfinished exercises. Each week continues from the previous week's end version. Optional topics use separate branches, such as `extra/week-03-redis`, and do not become requirements for the following week.

## References

- [Laravel documentation](https://laravel.com/docs/13.x)
- [PHP manual](https://www.php.net/manual/en/)

The reference links need internet access; the installed project runs locally.
