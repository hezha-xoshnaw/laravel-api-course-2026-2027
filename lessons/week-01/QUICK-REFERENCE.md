# Week 1 — Quick reference

Run commands from the project folder containing `artisan`.

| Command | Purpose |
| --- | --- |
| `php -v` | Check PHP; this project requires PHP 8.3+ |
| `php artisan --version` | Show the installed Laravel version |
| `composer check-platform-reqs` | Check PHP and extensions against the installed dependencies |
| `php artisan serve` | Start the local server |
| `php artisan serve --port=8001` | Use another port if 8000 is busy |
| `php artisan route:list` | List all registered routes |
| `php artisan route:list --path=api` | List routes with `api` in the path |
| `composer validate` | Check `composer.json` and its consistency with the lock file |
| `php artisan optimize:clear` | Clear generated framework and application caches |
| `php artisan test` | Run the application's tests |

Press **Ctrl+C** to stop the server. Use a second terminal while it is running.

## Files used today

| File | Role |
| --- | --- |
| `routes/web.php` | Remove the welcome-page route |
| `bootstrap/app.php` | Register the API route file |
| `routes/api.php` | Declare the hello route |
| `composer.json` | Adjust the setup and development shortcuts |
| `tests/Feature/ExampleTest.php` | Check the new endpoint instead of the welcome page |

## Postman request

```http
GET http://127.0.0.1:8000/api/hello
Accept: application/json
```

Expected status: **200 OK**.

```json
{"message":"Hello World"}
```

The route is written as `/hello` in `routes/api.php`. Laravel adds `/api` to the URL.

## Keep the offline copy working

Bring the complete prepared project, including `vendor/` and the local setup files. Do not delete `vendor/` or run `composer update` in class. Follow the [README](../../README.md) for first-time installation.

Follow [GUIDE.md](GUIDE.md) for the complete lesson.
