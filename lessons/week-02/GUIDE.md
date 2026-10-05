# Week 2 — From routes to controllers

**Core lesson:** approximately 2 hours. **Extensions:** Form Requests and API Resources, in class if time allows or as home practice.

Start from `week-02-start`. Its application code is the completed Week 1 code: only the hello endpoint has been implemented. This guide contains the steps you will carry out.

We will use a small ticket example throughout. A ticket describes a problem, such as a printer that is not working. Last week's preparation introduced the idea of accepting input and returning a response; tickets let us practise the same idea with both a list and a single item.

**No database or model is used in this lesson.** Our sample tickets live in a PHP array. They are recreated for every request. Sending a POST request will not change the list.

## Learning objectives

- Register routes with inline functions and controller methods.
- Read a route parameter and return either data or a 404 response.
- Explain why growing route functions become difficult to maintain.
- Generate a controller using Artisan.
- Accept JSON input and validate it before use.
- If time allows, move validation into a Form Request and format output with a Resource.

## 1. Start and check the application

Follow the project [README](../../README.md) if this is a new local installation. Prepare dependencies while internet is available.

In the project folder:

```sh
php artisan serve
```

In a second terminal:

```sh
php artisan route:list --path=api
```

In Postman, send `GET http://127.0.0.1:8000/api/hello` with `Accept: application/json`.

Expected response: **200 OK** and `{"message":"Hello World"}`.

Keep this hello route throughout the lesson. Do not run `install:api`, migrations, or Composer updates. The necessary routing and PHP packages are already available.

## 2. Return sample tickets from an inline function

An unnamed function passed directly to a route is called a **closure**. It is a valid way to handle a small endpoint. We will start here and reorganize the code when repetition becomes visible.

Open `routes/api.php`. Keep the existing imports and hello route. **Append** this route below them; do not add another `<?php` tag:

```php
Route::get('/tickets', function () {
    $tickets = [
        ['id' => 1, 'title' => 'Printer is offline', 'status' => 'open'],
        ['id' => 2, 'title' => 'Cannot sign in', 'status' => 'closed'],
    ];

    return response()->json($tickets);
});
```

`$tickets` is a list of associative arrays. Each ticket has named keys: `id`, `title`, and `status`. `response()->json()` converts the PHP data into JSON.

Send `GET /api/tickets`. Expected status: **200**.

```json
[
    {"id": 1, "title": "Printer is offline", "status": "open"},
    {"id": 2, "title": "Cannot sign in", "status": "closed"}
]
```

**Try it:** change one title in the code and send the request again. No database command is needed.

## 3. Add a route for one ticket

Now append another route to `routes/api.php`:

```php
Route::get('/tickets/{id}', function (string $id) {
    $tickets = [
        ['id' => 1, 'title' => 'Printer is offline', 'status' => 'open'],
        ['id' => 2, 'title' => 'Cannot sign in', 'status' => 'closed'],
    ];

    foreach ($tickets as $ticket) {
        if ((string) $ticket['id'] === $id) {
            return response()->json($ticket);
        }
    }

    return response()->json(['message' => 'Ticket not found'], 404);
})->whereNumber('id');
```

- `{id}` is a **route parameter**: `/tickets/2` supplies `2` to the function.
- URL parameters arrive as text. `(string)` converts the sample integer ID to text for an exact comparison with `===`.
- `foreach` examines each ticket. `return` ends the function when a match is found.
- If no ticket matches, we return an explanatory body with status **404**.
- `whereNumber('id')` limits matching to digit-only IDs. It checks the URL shape, not whether that ticket exists.

Send `GET /api/tickets/1`. Expected status: **200**.

```json
{"id": 1, "title": "Printer is offline", "status": "open"}
```

Send `GET /api/tickets/99`. Expected status: **404**.

```json
{"message":"Ticket not found"}
```

`GET /api/tickets/abc` also returns 404, but Laravel rejects the route before our function runs. Its error body need not be the same as our missing-ticket message.

### Stop and notice the problem

The routes work, but we have copied the same data into two places. If we edit only one copy, the list and single-ticket responses can disagree. The route file is also starting to contain searching and response logic.

A controller helps us group related handlers. Simply moving duplicated code would not remove duplication, so we will also put the sample array in one small method.

## 4. Generate and use a controller

Run:

```sh
php artisan make:controller TicketController
```

Artisan creates `app/Http/Controllers/TicketController.php`. It does not create the routes or implement their behavior.

Replace that file's contents with:

```php
<?php

namespace App\Http\Controllers;

class TicketController extends Controller
{
    public function index()
    {
        return response()->json($this->sampleTickets());
    }

    public function show(string $id)
    {
        foreach ($this->sampleTickets() as $ticket) {
            if ((string) $ticket['id'] === $id) {
                return response()->json($ticket);
            }
        }

        return response()->json(['message' => 'Ticket not found'], 404);
    }

    private function sampleTickets(): array
    {
        return [
            ['id' => 1, 'title' => 'Printer is offline', 'status' => 'open'],
            ['id' => 2, 'title' => 'Cannot sign in', 'status' => 'closed'],
        ];
    }
}
```

**PHP reminders:**

- `namespace` identifies where the class belongs; `use` imports a class name into another file.
- A function inside a class is a **method**. `index` and `show` are conventional names for listing and showing items.
- `public` methods can be called from outside the class. `private` makes the sample-data method internal to this class.
- `$this` refers to the current controller object. `->` calls its method.
- `: array` declares that `sampleTickets()` returns an array.

The helper method is temporary sample data, not a model or database replacement for a real application.

Now replace **all** of `routes/api.php` with:

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
Route::get('/tickets/{id}', [TicketController::class, 'show'])
    ->whereNumber('id');
```

`[TicketController::class, 'index']` identifies the class and method Laravel should call. Replacing the old ticket routes avoids registering duplicate handlers for the same method and path.

Run `php artisan route:list --path=api`. Repeat the list, existing-ID, and missing-ID requests. **Their responses should be unchanged.** We reorganized the implementation without changing what clients receive.

### Other approaches you may see

| Approach | When it is useful |
| --- | --- |
| Route closure | A small, simple handler such as our hello endpoint |
| Controller with named methods | Related actions such as listing and showing tickets; our approach today |
| Single-action controller (`__invoke`) | One controller dedicated to one action; optional reading |
| `Route::apiResource(...)` | A shortcut for registering standard CRUD routes; save it until we implement real CRUD |

**CRUD** means create, read, update, and delete. Generating an API controller does not implement those operations for you.

## 5. Accept JSON input and validate it

Next, the client will submit a proposed ticket. We will check its title and return a preview. We deliberately use `/tickets/preview` rather than pretending to save a record.

Add this import below the namespace in `TicketController.php`:

```php
use Illuminate\Http\Request;
```

Add this **public method inside the class**, before `sampleTickets()`:

```php
public function preview(Request $request)
{
    $data = $request->validate([
        'title' => ['required', 'string', 'max:100'],
    ]);

    return response()->json([
        'title' => $data['title'],
        'status' => 'open',
    ]);
}
```

Laravel supplies a `Request` object containing the incoming input. The rules require a title that is text and no longer than 100 characters. Validation returns the accepted input or stops the request with an error.

Append this line to `routes/api.php`:

```php
Route::post('/tickets/preview', [TicketController::class, 'preview']);
```

The GET route with `{id}` and this POST route have different methods. The numeric constraint also prevents `preview` from being treated as an ID in GET requests.

After registering the POST route, `GET /api/tickets/preview` returns **405 Method Not Allowed**: that path exists, but it accepts POST, not GET.

### Test valid input

In Postman:

| Setting | Value |
| --- | --- |
| Method | `POST` |
| URL | `http://127.0.0.1:8000/api/tickets/preview` |
| Headers | `Accept: application/json` and `Content-Type: application/json` |
| Body | Select **raw → JSON** |

Body:

```json
{"title":"Projector is not working"}
```

Expected status: **200 OK**.

```json
{"title":"Projector is not working","status":"open"}
```

`Accept` requests JSON output; `Content-Type` describes the JSON we are sending. This endpoint processes input but does not create a stored record, so we return 200 rather than 201 Created.

### Test invalid input

Send `{}`. Expected status: **422** and:

```json
{
    "message": "The title field is required.",
    "errors": {
        "title": ["The title field is required."]
    }
}
```

Also try `{"title":123}` and a title longer than 100 characters. Both should return 422. Send an extra field such as `"status":"closed"`: our preview still returns `open` because the controller sets that value; it does not copy arbitrary client fields.

Finally, request `GET /api/tickets` again. It still returns the original two tickets. **Nothing has been saved.**

## Core lesson checkpoint

You should be able to trace:

```text
Postman → route → controller method → input validation → JSON response
```

| Request | Expected result |
| --- | --- |
| `GET /api/hello` | 200, original greeting |
| `GET /api/tickets` | 200, two sample tickets |
| `GET /api/tickets/1` | 200, one ticket |
| `GET /api/tickets/99` | 404, `Ticket not found` |
| `POST /api/tickets/preview` with a valid title | 200, proposed ticket data |
| `POST /api/tickets/preview` without a title | 422, validation errors |

Run `php artisan test` to confirm that Week 1's hello test still passes. Those starter tests do **not** cover all these new endpoints; check the table in Postman too.

If class time is short, stop here. The following sections are extensions that build on this working version.

## 6. Extension: extract a Form Request

Our controller currently holds the validation rules. A **Form Request** gives those rules a dedicated class. Despite the name, it can validate JSON API input; it does not require an HTML form.

Generate the file:

```sh
php artisan make:request PreviewTicketRequest
```

Replace `app/Http/Requests/PreviewTicketRequest.php` with:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreviewTicketRequest extends FormRequest
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

`authorize()` decides whether this request is permitted. We return `true` because this public classroom example has no login or ownership rules. Leaving `false` returns **403 Forbidden** before validation.

In `TicketController.php`, replace the `Illuminate\Http\Request` import with:

```php
use App\Http\Requests\PreviewTicketRequest;
```

Replace only the `preview` method with:

```php
public function preview(PreviewTicketRequest $request)
{
    $data = $request->validated();

    return response()->json([
        'title' => $data['title'],
        'status' => 'open',
    ]);
}
```

Laravel runs this request's validation **before entering the controller method**. `$request->validated()` provides the accepted data. We do not repeat the same rules inside the controller.

Repeat the valid-title and missing-title requests. Responses should stay the same. Keep `index`, `show`, `sampleTickets`, and the routes unchanged.

## 7. Extension: format output with an API Resource

An **API Resource** defines the public shape of returned data. It can select fields and provide consistent formatting. It does not save data or validate incoming requests.

Generate one:

```sh
php artisan make:resource TicketResource
```

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
            'id' => $this->when(
                isset($this->resource['id']),
                fn () => $this->resource['id'],
            ),
            'title' => $this->resource['title'],
            'status' => $this->resource['status'],
        ];
    }
}
```

Here `$this->resource` is the PHP array passed into the Resource. We use array keys because we have no model.

`when(...)` includes `id` only when it exists. The short function `fn () => ...` reads the value only if the condition is true. Our existing sample tickets have IDs; an unsaved preview does not, so its JSON should not invent an ID.

Add this import to `TicketController.php`, keeping the Form Request import:

```php
use App\Http\Resources\TicketResource;
```

Make these three changes inside the controller:

**In `index`, replace its return statement:**

```php
return TicketResource::collection($this->sampleTickets());
```

**In `show`, replace only the successful match's return statement:**

```php
return new TicketResource($ticket);
```

Keep the existing 404 response after the loop.

**In `preview`, replace its return statement:**

```php
return new TicketResource([
    'title' => $data['title'],
    'status' => 'open',
]);
```

`new TicketResource(...)` formats one item. `TicketResource::collection(...)` formats a list of items. Neither needs a database model for this exercise.

### Notice the response change

Resources wrap successful output in a top-level `data` key by default.

`GET /api/tickets/1`:

```json
{
    "data": {
        "id": 1,
        "title": "Printer is offline",
        "status": "open"
    }
}
```

`GET /api/tickets`:

```json
{
    "data": [
        {"id": 1, "title": "Printer is offline", "status": "open"},
        {"id": 2, "title": "Cannot sign in", "status": "closed"}
    ]
}
```

A valid preview:

```json
{
    "data": {
        "title": "Projector is not working",
        "status": "open"
    }
}
```

The status remains 200. Validation errors and our missing-ticket response keep their previous shapes. `/api/hello` remains unchanged. Repeat all requests from the checkpoint table and check both the status and body.

## 8. Common mistakes and small recovery steps

| Problem | What to check |
| --- | --- |
| Controller class not found | File name, namespace, and the import in `routes/api.php` |
| Controller method not found | The method name in the route must match a public method |
| Unexpected handler runs | Remove old duplicate ticket routes; inspect `route:list` |
| 403 after adding the Form Request | Check `authorize()` |
| 422 with apparently valid input | Confirm the field is named `title` and Postman is sending raw JSON |
| POST returns 419 | Declare it in `routes/api.php`, not `routes/web.php` |
| JSON appears different after Resources | Expect the default `data` wrapper |
| A POST does not change later GET results | Expected: this lesson has no persistent storage |

Use editor undo for a recent accidental edit. If a generator creates a file you do not need, remove **that specific file** after removing any routes, imports, or other references to it. Artisan has no general undo command for generators.

To undo a refactor, put back the previous working code from the earlier section. For example, restore the inline-validation `preview` method and its `Request` import before deleting `PreviewTicketRequest.php`.

Do not overwrite a generated file with `--force` just to fix a mistake. If a file already exists, open and edit it. Keep a copy of your work before a large change, and avoid commands that discard all local changes.

## 9. Practice tasks

Choose a task in class or finish it at home:

1. **Core:** add a third ticket in `sampleTickets()`. Check both list and single-ticket endpoints.
2. **Core:** test unknown numeric IDs and nonnumeric IDs. Explain why both return 404 but follow different paths.
3. **Core:** accept an optional `description` with `nullable`, `string`, and `max:300`. Include it in the preview, handling missing input safely with `?? null`.
4. **Extension:** add an `internal_note` to the sample data. Keep it out of `TicketResource` and check that it is absent from the response.
5. **Extension:** move your description validation into the Form Request and include the public description in the Resource.

Do not implement a database or claim that the preview saves a ticket. Bring one example request that succeeds and one that fails validation to discuss.

## 10. Review and next lecture

Be ready to explain:

- What stays in the route file after introducing a controller?
- How does the controller receive the requested ID?
- What does Artisan generate, and what must you still write?
- When does a Form Request validate input?
- How is an API Resource different from a Form Request?
- Why does our preview not appear in the ticket list?

Next lecture, we will introduce databases, migrations, and models. Stored records will replace these temporary arrays, and creating a ticket will actually persist data.

### Suggested core timing

| Minutes | Activity |
| --- | --- |
| 0–10 | Start and review hello |
| 10–35 | Inline list and single-ticket routes |
| 35–65 | Generate the controller and move the handlers |
| 65–95 | JSON input and inline validation |
| 95–110 | Success/error checks in Postman |
| 110–120 | Exercise and recap |

Use the extension sections if the class progresses faster, or complete them at home. See [QUICK-REFERENCE.md](QUICK-REFERENCE.md) while practising.
