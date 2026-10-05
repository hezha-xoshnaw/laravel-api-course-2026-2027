# Week 2 — Commands and patterns

Run commands in the folder containing `artisan`. The [guide](GUIDE.md) explains when to use each one.

## Artisan commands

| Command | Purpose |
| --- | --- |
| `php artisan serve` | Start the local server |
| `php artisan route:list --path=api` | Inspect registered API routes and their handlers |
| `php artisan list` | List available commands |
| `php artisan help make:controller` | Read a command's options |
| `php artisan make:controller TicketController` | Generate the plain controller used in this lesson |
| `php artisan make:request PreviewTicketRequest` | Generate the extension's validation class |
| `php artisan make:resource TicketResource` | Generate the extension's response-formatting class |
| `php artisan route:clear` | Clear previously cached routes if edits are not appearing |
| `php artisan test` | Run existing tests; use Postman to check new lesson endpoints too |

Use a second terminal while the server is running. Press **Ctrl+C** to stop it.

There is no standard `php artisan make:route` command. Write routes in `routes/api.php`. Generating a controller does not register routes for it.

Optional command forms to recognize, not additional steps to run today:

- `php artisan make:controller ExampleController --invokable` — generates one `__invoke` action.
- `php artisan make:controller ExampleController --api` — generates conventional CRUD method stubs; it does not implement them or create routes.

## Route patterns

These are alternatives to understand, not duplicate routes to paste together:

```php
// Inline function:
Route::get('/tickets', function () {
    return response()->json([]);
});

// Controller method:
Route::get('/tickets', [TicketController::class, 'index']);

// Controller method with a numeric route parameter:
Route::get('/tickets/{id}', [TicketController::class, 'show'])
    ->whereNumber('id');
```

The route file needs `use Illuminate\Support\Facades\Route;` and, for controller routes, `use App\Http\Controllers\TicketController;`. Laravel adds the `/api` prefix.

## Responsibilities

| Part | Job |
| --- | --- |
| Route | Match the method/path and select a handler |
| Controller | Handle the operation and produce a response |
| `Illuminate\Http\Request` | Represent the incoming request |
| Form Request | Authorize and validate input before the controller method runs |
| API Resource | Select and format returned data |

In this lesson, `authorize()` returns `true`. Validation checks `title` with `required`, `string`, and `max:100`. A Resource formats one array with `new TicketResource($ticket)` or a list with `TicketResource::collection($tickets)`.

## Postman checks

Base URL: `http://127.0.0.1:8000`. Always send `Accept: application/json`.

| Method and path | Expected |
| --- | --- |
| `GET /api/hello` | 200, original greeting |
| `GET /api/tickets` | 200, list of sample tickets |
| `GET /api/tickets/1` | 200, first ticket |
| `GET /api/tickets/99` | 404, no matching ticket |
| `POST /api/tickets/preview` with a valid title | 200, unsaved preview |
| `POST /api/tickets/preview` with `{}` | 422, validation errors |

For POST, choose **Body → raw → JSON**, send `Content-Type: application/json`, and use:

```json
{"title":"Projector is not working"}
```

After the Resource extension, successful ticket responses gain a top-level `data` wrapper. The preview has no ID because nothing is saved. GET requests continue returning the original sample data.

## Undo tips

- Use editor undo for recent edits.
- Remove an unwanted generated file only after removing its references.
- Restore the earlier working snippet to reverse a refactor.
- Do not use `--force` to overwrite existing work casually.
- Keep a backup of your exercises before changing branches or discarding edits.

No migrations, model generation, dependency downloads, or frontend build commands are required for this lesson.
