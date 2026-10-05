# Week 1 — Prepare Laravel for a REST API

**Duration:** approximately 2 hours

This lesson starts with a fresh Laravel 13 application. We will explore its directories, remove the frontend files we do not need, and create one endpoint that returns JSON.

The starting application has not been cleaned up yet. Follow the steps below to make the changes yourself.

## Learning objectives

By the end of the lesson, you should be able to:

- Identify the main Laravel directories.
- Explain the difference between a web page and an API response.
- Register an API route and return JSON.
- Send a request using Postman and check its response.

We will introduce controllers, validation, Form Requests, and API Resources in Week 2.

## 1. Check the starting application

Complete the first-time setup in the project [README](../../README.md) before class. Dependencies must be installed while internet is available.

Open a terminal in the folder containing `artisan`, then run:

```sh
php -v
php artisan --version
php artisan route:list
php artisan serve
```

PHP should be 8.3 or newer. Open **http://127.0.0.1:8000** and confirm that the Laravel welcome page loads.

Keep the server terminal open. Use a second terminal for other commands. Press **Ctrl+C** when you want to stop the server.

The baseline uses SQLite. This lesson does not need MySQL, Apache, Node.js, or an NPM build.

## 2. Understand the request and response

A **client**, such as Postman, sends a request. Laravel finds the matching **route**, executes its code, and returns a response.

```text
Postman → local web server → Laravel → route → JSON response
```

A traditional web route might return an HTML page. Our API will return data as **JSON**, a text format that other programs can read.

An **endpoint** is a particular API address used with an HTTP method. Today's endpoint will be:

```text
GET http://127.0.0.1:8000/api/hello
```

`GET` asks to read something. `/api/hello` is the path. `8000` is the port used by our local server.

## 3. Explore the directories

Open these items in your editor. You do not need to understand every file yet.

| Location | Purpose |
| --- | --- |
| `app/` | Application PHP code |
| `app/Http/Controllers/` | Classes that will handle requests in later lessons |
| `app/Models/` | Classes for working with database records |
| `app/Providers/` | Application setup and registration |
| `bootstrap/app.php` | Configures application startup, routing, middleware, and errors |
| `bootstrap/cache/` | Generated framework cache files |
| `config/` | Settings for databases, logging, sessions, and other features |
| `database/migrations/` | Instructions for creating or changing database tables |
| `database/seeders/` | Code for inserting initial or practice data |
| `database/factories/` | Code for generating sample records |
| `public/index.php` | Entry point for incoming web requests |
| `resources/views/` | Blade templates used to build HTML pages |
| `resources/css/` and `resources/js/` | Frontend source files |
| `routes/web.php` | Routes intended for web pages |
| `routes/console.php` | Console command definitions |
| `storage/` | Logs, compiled views, and other runtime files |
| `tests/` | Automated checks of the application |
| `vendor/` | Installed PHP dependencies; do not edit these files |
| `.env` | Settings for this local installation; do not publish it |
| `.env.example` | Example settings for new installations |
| `artisan` | Laravel's command-line entry point |
| `composer.json` | Required PHP packages and project scripts |
| `composer.lock` | Exact dependency versions used by the project |

Notice that the fresh Laravel 13 application does not have `routes/api.php` yet. We will add it below.

## 4. Remove the welcome-page route

Open `routes/web.php`. Its `/` route currently returns the `welcome` Blade view.

**Blade** is Laravel's template system for generating HTML. Our API does not need that page.

Replace the file contents with:

```php
<?php

// This course application uses API routes.
```

The root address `http://127.0.0.1:8000` will now return **404 Not Found**. This is expected: we removed its route.

Remove the route before deleting its view so Laravel does not try to load a missing template.

## 5. Remove unnecessary frontend files

Use the editor's file explorer to delete these items from your working project:

| Item | Why it is unnecessary for this API |
| --- | --- |
| `resources/views/` | Contains the welcome Blade template |
| `resources/css/` | Contains frontend styles |
| `resources/js/` | Contains frontend JavaScript |
| `package.json` | Lists frontend packages and build scripts |
| `vite.config.js` | Configures Vite, the frontend build tool |
| `.npmrc` | Settings for the NPM package manager |
| `package-lock.json`, `node_modules/`, `public/build/`, `public/hot` — if present | Frontend dependency or build files |

The `resources/` folder may remain empty. Keep `public/`, especially `public/index.php`: an API still needs its web entry point.

Keep the backend directories, `.env`, `composer.json`, `composer.lock`, and `vendor/`. Keep the default models and migrations too; we will explain database work later.

### Update the Composer shortcuts

Open `composer.json`. Inside `scripts`, the default `setup` shortcut contains NPM commands. Remove only these two entries from that array:

```json
"npm install --ignore-scripts",
"npm run build"
```

Remove the trailing comma after the new last entry, `"@php artisan migrate --force"`, so the file remains valid JSON.

The default `dev` shortcut launches additional development processes. Replace its array with:

```json
"dev": [
    "Composer\\Config::disableProcessTimeout",
    "@php artisan serve"
]
```

Keep the other scripts and dependency settings unchanged. Preserve the comma after this array if another entry follows it.

Check the file and clear generated caches:

```sh
composer validate
php artisan optimize:clear
```

These edits change shortcuts, not package requirements. Do not run `composer update` or remove packages during this lesson.

## 6. Register an API route file

For one public hello endpoint, we can register API routes directly without installing an authentication package. This also works offline.

Create `routes/api.php` with:

```php
<?php

use Illuminate\Support\Facades\Route;
```

Open `bootstrap/app.php`. Inside `withRouting(...)`, add the `api` entry immediately after the `web` entry:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

This is only the routing section, not the complete file. Keep the rest of `bootstrap/app.php` unchanged.

Laravel now loads `routes/api.php`, applies its API middleware group, and adds the `/api` URL prefix. **Middleware** is processing that runs around a request; we will study it later.

Laravel also provides `php artisan install:api`. That command installs Sanctum for authentication and creates API scaffolding. We do not run it for this small lesson because no authentication package is needed. We can introduce it when authentication is taught.

## 7. Add the Hello World route

Replace `routes/api.php` with:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/hello', function () {
    return response()->json([
        'message' => 'Hello World',
    ]);
});
```

- `Route::get(...)` registers a route that accepts GET requests.
- `'/hello'` is the path inside the API route file.
- `function () { ... }` contains the code that runs for this request.
- `response()->json(...)` creates a JSON response from the PHP array.
- `return` sends that response back through Laravel to the client.

Do not write `/api/hello` in the route declaration: Laravel already adds `/api`.

Check that the route is registered:

```sh
php artisan route:list --path=api
```

You should see `GET|HEAD` for `api/hello`. HEAD is related to GET but returns headers without the response body.

## 8. Test in Postman

Create a request with:

| Setting | Value |
| --- | --- |
| Method | `GET` |
| URL | `http://127.0.0.1:8000/api/hello` |
| Header | `Accept: application/json` |
| Body | None |

Click **Send**. Expected status: **200 OK**.

```json
{
    "message": "Hello World"
}
```

The `Accept` header says which response format the client wants. We will use it throughout the API course, including when checking error responses.

The endpoint does not read from the database. It simply returns the array in our route code.

## 9. Update the existing starter test

The default test expects `/` to return 200. That no longer matches our application because we removed the welcome route.

Open `tests/Feature/ExampleTest.php`. Change:

```php
$response = $this->get('/');
```

to:

```php
$response = $this->getJson('/api/hello');
```

Keep its status assertion and add a response-data assertion:

```php
$response->assertStatus(200);
$response->assertExactJson(['message' => 'Hello World']);
```

Run:

```sh
php artisan test
```

This small adjustment keeps the starter test useful. We will study automated testing more deeply later.

## 10. Check your work

- `GET /api/hello` returns 200 and the expected JSON.
- `GET /` returns 404 because the welcome route was removed.
- `php artisan route:list --path=api` lists the hello endpoint.
- `composer validate` reports valid configuration.
- `php artisan test` passes.
- No NPM install or frontend build is required.

| Problem | Check |
| --- | --- |
| Postman cannot connect | Keep `php artisan serve` running and check its port |
| `/api/hello` returns 404 | Check `bootstrap/app.php` and the route list |
| The route appears as `/api/api/hello` | Remove `/api` from the route declaration |
| The old welcome-page test fails | Update it to request `/api/hello` |
| Composer reports invalid JSON | Check commas around the edited scripts |

## Exercise and preparation

Add a second GET endpoint, `/api/course`, that returns a course name and week number. Verify the response in Postman. No controller or database is needed.

Before Week 2, review PHP arrays, functions, classes, and methods. Read about GET versus POST and the purpose of HTTP headers and request bodies.

Next week, we will move request-handling code into a controller, accept input, validate it, and introduce Form Requests and API Resources.

## Suggested timing

| Minutes | Activity |
| --- | --- |
| 0–15 | Start Laravel and review requests/responses |
| 15–40 | Directory tour |
| 40–65 | Frontend cleanup |
| 65–85 | Register API routes and add hello |
| 85–105 | Postman and starter test |
| 105–120 | Exercise and recap |

See [QUICK-REFERENCE.md](QUICK-REFERENCE.md) for the commands used in this lesson.
