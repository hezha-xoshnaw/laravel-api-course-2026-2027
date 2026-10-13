# Week 3 — Commands and patterns

Follow the [guide](GUIDE.md) in order. These commands generate files or apply the code you write; they do not implement the whole feature automatically.

## Commands

| Command | Purpose |
| --- | --- |
| `php artisan serve` | Start the development server |
| `php artisan config:clear` | Clear cached configuration after local connection changes |
| `php artisan make:model Ticket -m` | Generate a model and migration |
| `php artisan migrate` | Apply pending migrations |
| `php artisan migrate:status` | See which migrations have run |
| `php artisan make:seeder TicketSeeder` | Generate a seeder |
| `php artisan db:seed` | Run DatabaseSeeder and the seeders it calls |
| `php artisan db:seed --class=TicketSeeder` | Run only TicketSeeder |
| `php artisan make:request StoreTicketRequest` | Generate the creation Form Request |
| `php artisan route:list --path=api` | Inspect API routes |

Edit generated files before migrating or seeding. No new Composer packages are required.

## Eloquent patterns

```php
Ticket::orderBy('id')->get();          // Collection of stored tickets.
Ticket::findOrFail($id);              // One record, or a 404 when unhandled in an HTTP request.
Ticket::create($request->validated()); // Insert accepted fields; requires fillable configuration.
$ticket->refresh();                   // Reload values from the database.
```

Import `App\Models\Ticket` where these examples are used. `findOrFail` is an alternative lookup pattern; the guide uses route model binding instead:

```php
Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
    ->whereNumber('ticket');

// Inside TicketController:
public function show(Ticket $ticket)
{
    return new TicketResource($ticket);
}
```

The parameter name and argument name must match. Keep the imports shown in the full guide.

## Final endpoints

| Method and path | Result |
| --- | --- |
| `GET /api/hello` | 200, original greeting |
| `GET /api/tickets` | 200, stored tickets inside `data` |
| `GET /api/tickets/{id}` | 200 for an existing ID; otherwise 404 |
| `POST /api/tickets` | 201 with a stored ticket; 422 for invalid input |

For POST, send JSON with `Accept: application/json` and `Content-Type: application/json`:

```json
{"title":"Projector is not working"}
```

The title must be a nonempty string of at most 100 characters. The database assigns the ID and defaults status to `open`. The old preview endpoint is removed during this lesson.

## Remember

- Migration: structure. Seeder: initial data. Model: database operations.
- Form Request: input validation. Resource: output fields.
- SQLite data stays in `database/database.sqlite` between requests.
- Do not commit `.env` or your local database.
- `migrate` applies pending changes; it does not rerun edited migrations.
- Rollback and fresh commands can delete data; they are not routine startup steps.
