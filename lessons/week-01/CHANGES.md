# Week 1 — Completed changes

The application now returns a JSON response from one public API endpoint. It no longer serves the Laravel welcome page or needs frontend build tools.

Use [GUIDE.md](GUIDE.md) to follow the lesson from its starting point, or [QUICK-REFERENCE.md](QUICK-REFERENCE.md) to review the commands.

## What changed and why

| File or directory | Change |
| --- | --- |
| `routes/web.php` | Removed the welcome-page route because this application will serve an API |
| `resources/views/`, `resources/css/`, `resources/js/` | Removed the Blade page and frontend source files |
| `package.json`, `vite.config.js`, `.npmrc` | Removed frontend dependency and build configuration |
| `composer.json` | Removed NPM steps from `setup`; changed `dev` to run `php artisan serve` |
| `bootstrap/app.php` | Registered `routes/api.php` with Laravel's API routing configuration |
| `routes/api.php` | Added a GET route that returns a Hello World JSON response |
| `tests/Feature/ExampleTest.php` | Changed the starter test to check the API response instead of the removed welcome page |
| `README.md` | Updated startup instructions for this completed API version |

The PHP dependencies and their versions are unchanged. Registering this route did not require a package download. The default models, migrations, and SQLite configuration remain for later lessons.

## Run and check the result

After completing the first-time setup in the [README](../../README.md), run:

```sh
php artisan serve
```

Send this request from Postman:

```http
GET http://127.0.0.1:8000/api/hello
Accept: application/json
```

Expected status: **200 OK**.

```json
{
    "message": "Hello World"
}
```

The root URL `/` returns **404 Not Found** because its welcome-page route was removed. Use `/api/hello` to check the application.

In a second terminal:

```sh
composer validate
php artisan route:list --path=api
php artisan test
```

The API route list should include `GET|HEAD api/hello`, and the tests should pass. No NPM build is needed.

## What this demonstrates

```text
Client → HTTP request → Laravel → matching route → JSON response
```

The route returns a PHP array as JSON. It does not query a database, require login, or use a custom controller yet. Laravel supplies the `/api` prefix, so the route declaration itself uses `/hello`.

The optional `/api/course` exercise is left for students; it is not included in this completed example.

## Before Week 2

### Read and review

Aim to explain these ideas in a few sentences. You do not need to build the Week 2 features yet.

| Topic | What to focus on |
| --- | --- |
| REST APIs | Resources, endpoints, JSON, and the purpose of HTTP methods |
| Requests and responses | GET versus POST, headers, request bodies, and status codes such as 200, 404, and 422 |
| Routes | How a method and URL select the code that handles a request |
| Controllers | Why request-handling code can move out of a route and into a class method |
| Basic PHP | Variables, arrays, functions, classes, methods, namespaces, and `use` statements |
| Form Requests | The idea of checking incoming data before using it |
| API Resources | The idea of controlling the shape of the JSON we return |

Optional PHP reading:

- [PHP arrays](https://www.php.net/manual/en/language.types.array.php)
- [PHP functions](https://www.php.net/manual/en/language.functions.php)
- [PHP classes and objects](https://www.php.net/manual/en/language.oop5.basic.php)

The [Laravel documentation](https://laravel.com/docs/13.x) has sections on routing, controllers, validation, and Eloquent API Resources. Skim their introductions; detailed implementation comes in the next lecture. Online references require internet access.

### Practice: set up the fresh application again

1. Keep your completed Week 1 work. Use a separate folder named `week-01-practice`.
2. Download the course repository's `main` version into that folder. It contains the fresh Laravel baseline, before cleanup.
3. Follow its README: run `composer install`, prepare `.env`, generate the application key, create the SQLite database file, and run migrations.
4. Start the server and confirm the welcome page loads.
5. Repeat the cleanup and hello-route steps from [GUIDE.md](GUIDE.md).
6. Check `/api/hello` in Postman and run `php artisan test`.

This is a fresh installation of the course application using its fixed dependency versions. You do not need to reinstall PHP or Composer. Complete the download and dependency installation while internet is available.

Try the steps yourself first, then use the completed Week 1 code to check your work. Write down anything you could not explain or get working.

### Preview: useful Artisan commands

Artisan is Laravel's command-line tool. Run it from the folder containing `artisan`.

| Command | Purpose |
| --- | --- |
| `php artisan list` | See available commands |
| `php artisan help make:controller` | Read the options for a command |
| `php artisan serve` | Start the local application |
| `php artisan route:list --path=api` | Inspect registered API routes |
| `php artisan make:controller HelloController` | Generate a controller file |
| `php artisan make:request GreetRequest` | Generate a Form Request file for validation |
| `php artisan make:resource GreetingResource` | Generate an API Resource file for response formatting |

The three `make:` commands are a preview. If you try them, use your practice folder. Generating a file does not implement the feature or register an endpoint.

**Routes are written in `routes/api.php`; Artisan does not provide a standard `make:route` command.** `route:list` shows routes that have already been registered. We will connect a route to a controller during Week 2.

### What we will build next

We will accept a name and return a greeting. First we will use a controller and validate input there. Then we will move validation into a Form Request and format the response with an API Resource. The Week 2 guide will provide the code and detailed steps.

### What to bring to the next lecture

- Read this preparation section after completing Week 1.
- When `week-02-start` is published before the next lecture, download or check out that version and read its guide.
- Follow that version's setup instructions while online. Bring the working folder with its installed dependencies and local setup files, plus Postman.
- Keep your practice work separately; start the class from `week-02-start`, not your experimental copy.

The same pattern continues each week: use the **start** version for the lecture, then review the **end** version and its preparation notes afterward.
