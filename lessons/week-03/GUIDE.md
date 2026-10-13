# Week 3 — Store tickets with Eloquent

**Duration:** approximately 2 hours. Start from `week-03-start`. Its application code is the completed Week 2 example; the steps below have not been implemented yet.

Last week, ticket data lived in an array and POST returned an unsaved preview. This week, we will store tickets in SQLite and retrieve them through Laravel's Eloquent models.

## What we will build

- A migration describing the `tickets` table.
- A `Ticket` model for reading and writing records.
- A small seeder containing two classroom examples.
- Controller methods that list, show, and create stored tickets.
- A Form Request for creation and a Resource for output.

We will keep SQLite so no separate database server is required. Relationships, login, factories, automated tests, update, and delete are outside this lesson.

## 1. Start and review

Follow the [README](../../README.md) for initial setup if needed. For this lecture, use `week-03-start`.

```sh
php -v
php artisan serve
```

In Postman, check `GET http://127.0.0.1:8000/api/tickets`. At this point it still returns Week 2's two static tickets. Keep `Accept: application/json` on all requests.

Open a second terminal for Artisan commands. Run commands from the folder containing `artisan`.

## 2. Understand the parts

| Part | Purpose |
| --- | --- |
| Database | Stores data between requests and server restarts |
| Table | Groups records with the same columns |
| Migration | Describes a database structure change in PHP |
| Model | Represents records and queries through Eloquent |
| Seeder | Inserts example or initial data |
| Controller | Coordinates an HTTP operation |
| Form Request | Validates incoming input |
| Resource | Defines the public response fields |

Eloquent is Laravel's object-relational mapper: PHP objects represent database records. By convention, the `Ticket` model uses the `tickets` table and its `id` primary key.

A model does not create its table. A migration does not automatically populate it. A seeder supplies data after the table exists.

## 3. Check the SQLite connection

In your local `.env`, use:

```dotenv
DB_CONNECTION=sqlite
```

For the default file location, leave `DB_DATABASE` commented out as in `.env.example`. If you previously set it to another database name or a path from another PC, remove that active setting. Laravel will use `database/database.sqlite`.

```sh
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan config:clear
php artisan migrate:status
```

Enable `pdo_sqlite` and `sqlite3` in the PHP installation used by your terminal. Use `php --ini` to locate its configuration if needed. SQLite needs no MySQL username, password, or running Apache service.

If the migration table does not exist on a new setup, run `php artisan migrate`, then check the status again. Keep your local database and `.env` out of Git.

## 4. Generate a model and migration

```sh
php artisan make:model Ticket -m
```

`-m` also generates a migration. Open the new file ending in `create_tickets_table.php` inside `database/migrations/`. Its timestamp will differ for each student.

Replace its contents with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('status')->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
```

- `up()` applies the change; `down()` describes its reversal.
- `id()` creates an automatically generated primary key.
- `title` holds the ticket text. We also enforce its length in request validation; SQLite does not enforce a VARCHAR length limit.
- `status` defaults to `open` when omitted from an insert.
- `timestamps()` adds `created_at` and `updated_at`, which Eloquent maintains.

Apply the migration:

```sh
php artisan migrate
php artisan migrate:status
```

`migrate` runs pending migrations. Running it again does not recreate an already migrated table. The API still uses its array until we change the controller.

## 5. Configure the model

Replace `app/Models/Ticket.php` with:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title'])]
class Ticket extends Model
{
}
```

This uses Laravel 13's `Fillable` attribute, also used by the starter User model. It permits `title` when assigning an array through methods such as `Ticket::create(...)`. Older examples may use the equivalent `protected $fillable` property.

Fillable is not validation. The Form Request will check input; fillable controls mass assignment. We allow clients to supply only a title. The database supplies the default status.

No manual table name or SQL is needed for this conventional model.

## 6. Add a small seeder

```sh
php artisan make:seeder TicketSeeder
```

Replace `database/seeders/TicketSeeder.php` with:

```php
<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['title' => 'Printer is offline', 'status' => 'open'],
            ['title' => 'Cannot sign in', 'status' => 'closed'],
        ] as $sample) {
            $ticket = Ticket::firstOrNew(['title' => $sample['title']]);
            $ticket->status = $sample['status'];
            $ticket->save();
        }
    }
}
```

`firstOrNew` finds a matching title or makes an unsaved model. `save()` inserts a new record or updates an existing one. Assigning `status` explicitly here is trusted seeder code, not client input.

For these classroom examples, rerunning the seeder avoids another copy of the same title and resets their statuses. Titles are not unique database keys; this simple matching approach is only for our sample data.

Replace `database/seeders/DatabaseSeeder.php` with:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(TicketSeeder::class);
    }
}
```

This replaces the starter test-user creation step. It does not delete existing users. Our public ticket example needs no generated user.

```sh
php artisan db:seed
```

`db:seed` runs `DatabaseSeeder`. To run only the ticket seeder later, use `php artisan db:seed --class=TicketSeeder`.

## 7. Prepare creation input and model output

We are replacing the unsaved preview with actual creation. Generate a request with a name that matches the new operation:

```sh
php artisan make:request StoreTicketRequest
```

Replace `app/Http/Requests/StoreTicketRequest.php` with:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
        ];
    }
}
```

The rules match last week. This public teaching example still permits requests without login.

Replace `app/Http/Resources/TicketResource.php` with:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
        ];
    }
}
```

Every ticket returned by the new controller will be a saved model, so every response includes its ID. Resource property access reads the underlying model. Timestamps exist in the database but are not exposed here.

Complete the controller and route changes below before sending requests: the old preview still supplies an array until it is replaced.

## 8. Connect the controller to Eloquent

Replace `app/Http/Controllers/TicketController.php` with:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;

class TicketController extends Controller
{
    public function index()
    {
        return TicketResource::collection(Ticket::orderBy('id')->get());
    }

    public function show(Ticket $ticket)
    {
        return new TicketResource($ticket);
    }

    public function store(StoreTicketRequest $request)
    {
        $ticket = Ticket::create($request->validated());
        $ticket->refresh();

        return (new TicketResource($ticket))
            ->response()
            ->setStatusCode(201);
    }
}
```

- `orderBy('id')->get()` retrieves a collection of stored tickets in ID order. This small example returns the whole list; pagination comes later.
- `show(Ticket $ticket)` receives the model through route model binding, configured in the next step.
- `create()` inserts validated data. Avoid `$request->all()` here.
- `refresh()` reloads the stored record, including the database's `open` default.
- **201 Created** tells the client a record was saved. Resource formatting stays in the Resource; this explicit status belongs to the controller's creation response.

The sample-data method and preview method are now gone. Seed data lives in the seeder; application requests read the database.

## 9. Register the routes

Replace `routes/api.php` with:

```php
<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/hello', function () {
    return response()->json([
        'message' => 'Hello World',
    ]);
});

Route::get('/tickets', [TicketController::class, 'index']);
Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
    ->whereNumber('ticket');
Route::post('/tickets', [TicketController::class, 'store']);
```

The parameter name `{ticket}` matches `$ticket` in `show(Ticket $ticket)`. Laravel finds the record by ID and supplies the model. If it cannot find one, Laravel returns 404 before the method runs. The message may differ from Week 2's custom `Ticket not found` response.

`POST /api/tickets` replaces `POST /api/tickets/preview`. The old preview URL now returns 404. GET list and detail URLs stay the same.

Delete the now-unused `app/Http/Requests/PreviewTicketRequest.php` in your editor. Its import, method, and route were removed by the replacements above. Keep `StoreTicketRequest.php`.

```sh
php artisan route:list --path=api
```

Explicit routes keep these three operations visible. `Route::apiResource` is another option when we introduce the remaining CRUD operations.

## 10. Check persistence in Postman

Base URL: `http://127.0.0.1:8000`. Send `Accept: application/json`.

1. Send `GET /api/tickets`. Expect 200 and seeded tickets inside `data`. Use the IDs returned; do not assume they start at 1 in an existing database.
2. Send `GET /api/tickets/{id}` using one returned ID. Expect 200 and that ticket.
3. Send `POST /api/tickets`, with **Body → raw → JSON** and `Content-Type: application/json`:

```json
{"title":"Projector is not working"}
```

Expected status: **201 Created**. An example body is:

```json
{
    "data": {
        "id": 3,
        "title": "Projector is not working",
        "status": "open"
    }
}
```

The actual ID may differ. Copy it and request that ticket. Request the list again: the new ticket is now present. Stop and restart `php artisan serve`; the ticket remains in the SQLite file.

| Additional check | Expected |
| --- | --- |
| POST with `{}` | 422, missing title |
| POST with a numeric title or more than 100 characters | 422, invalid title |
| POST with a valid title and `"status":"closed"` | 201, status stays `open` |
| GET an ID that does not exist | 404 from model binding |
| GET `/api/tickets/abc` | 404 from the numeric constraint |
| POST `/api/tickets/preview` | 404; preview was removed |
| GET `/api/hello` | Original 200 greeting |

Each successful POST creates another record, even if the title repeats. Failed validation should leave the list unchanged.

## 11. Common mistakes and recovery

| Problem | What to check |
| --- | --- |
| Could not find driver | Enable SQLite extensions in the PHP used by the terminal |
| No such table: tickets | Run the new migration and check `migrate:status` |
| Wrong or empty database | Check local `.env`, remove an old `DB_DATABASE` override, and run `config:clear` |
| Seed data is missing | Run `db:seed` after migration |
| Mass assignment exception | Check the model's `Fillable` import and attribute |
| 403 on creation | Check `StoreTicketRequest::authorize()` |
| A valid existing ID returns unexpected data | Match `{ticket}` to `Ticket $ticket` and check the selected database |
| Creation response lacks status | Keep `refresh()` after `create()` to read the database default |
| Old preview no longer works | Use `POST /api/tickets` |

Use editor undo for accidental code changes. Editing a migration that already ran does not rerun it: for later shared schema changes, create a new migration.

`migrate:rollback` reverses the last batch and can remove tables and data. `migrate:fresh` drops all tables in the selected database. Neither is required for this lesson. Check the connection and keep a backup before deliberately resetting a disposable practice database.

## Practice and recap

- Add a third sample to `TicketSeeder`, run it, and find the record through the API.
- Run the seeder twice and explain why the two original titles are not duplicated by this seeder.
- Compare the controller with Week 2: where did the data and manual search loop go?
- Explain why Form Request validation and model fillable settings have different jobs.
- Explain why the preview returned 200 while creation returns 201.

Next, we can build on these stored records with update and delete operations. Keep this week's scope to listing, showing, and creating tickets.

## Suggested timing

| Minutes | Activity |
| --- | --- |
| 0–15 | Review and check the SQLite connection |
| 15–45 | Migration and model |
| 45–60 | Small seeder |
| 60–95 | Request, Resource, controller, and routes |
| 95–115 | Postman checks and persistence |
| 115–120 | Recap and practice |

## References

- [Laravel migrations](https://laravel.com/docs/13.x/migrations)
- [Laravel Eloquent](https://laravel.com/docs/13.x/eloquent)
- [Laravel seeding](https://laravel.com/docs/13.x/seeding)
- [Route model binding](https://laravel.com/docs/13.x/routing#route-model-binding)

Online references require internet access. Use [QUICK-REFERENCE.md](QUICK-REFERENCE.md) for the lesson commands.
