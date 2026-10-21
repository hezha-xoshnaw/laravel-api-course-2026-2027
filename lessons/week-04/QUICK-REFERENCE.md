# Week 4 — Commands and patterns

Follow the [guide](GUIDE.md) in order. The start branch still has Week 3's list, detail, and creation endpoints; update and delete are implemented during the lesson.

## Commands

| Command | Purpose |
| --- | --- |
| `php artisan serve` | Start the development server |
| `php artisan migrate:status` | Check that the existing migrations ran |
| `php artisan make:request UpdateTicketRequest` | Generate the update validation class |
| `php artisan route:list --path=api` | Inspect API methods, URLs, and handlers |
| `php artisan help make:request` | Read generator options |
| `php artisan route:clear` | Clear cached routes if previously cached routes hide edits |

Edit the existing controller and model. There is no need to regenerate either one, add a migration, reinstall packages, or reset the database.

## Partial-update rules

Inside `UpdateTicketRequest::rules()`:

```php
return [
    'title' => ['sometimes', 'required', 'string', 'max:100'],
    'status' => ['sometimes', 'required', 'string', 'in:open,closed'],
];
```

`sometimes` permits omission; `required` rejects an empty supplied value. Keep `authorize()` returning `true` for this public example. An empty object is accepted as a no-op in this lesson.

Change the existing model attribute to `#[Fillable(['title', 'status'])]`. Creation still accepts only title through `StoreTicketRequest`.

## Controller patterns

Add `use App\Http\Requests\UpdateTicketRequest;` to the controller. Keep its existing Ticket and TicketResource imports.

```php
public function update(UpdateTicketRequest $request, Ticket $ticket)
{
    $ticket->update($request->validated());

    return new TicketResource($ticket);
}

public function destroy(Ticket $ticket)
{
    $ticket->delete();

    return response()->noContent();
}
```

These methods go inside `TicketController`. Keep `index`, `show`, and `store`.

## Routes to append

```php
Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])
    ->whereNumber('ticket');
Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])
    ->whereNumber('ticket');
```

Keep the existing imports, hello route, and Week 3 routes. `{ticket}` matches the bound `Ticket $ticket` argument.

## Postman checks

Use `http://127.0.0.1:8000` with `Accept: application/json`. For POST and PATCH, select **Body → raw → JSON** and send `Content-Type: application/json`.

| Request | Expected |
| --- | --- |
| POST a valid title to `/api/tickets` | 201; copy its ID |
| PATCH that ID with `{"status":"closed"}` | 200; title stays unchanged |
| PATCH that ID with `{"title":"Repaired"}` | 200; status stays unchanged |
| PATCH that ID with `{"status":"pending"}` | 422; stored data stays unchanged |
| PATCH that ID with `{"title":""}` | 422 |
| PATCH that ID with `{}` | 200; no changes |
| DELETE that ID | 204; empty body |
| GET or DELETE the deleted ID | 404 |

Replace `{id}` with an ID returned by the API. GET list and detail still return Resource data with 200. PUT is not registered in this lesson.

## Remember

- Pass `$request->validated()` to update; omitted fields remain unchanged.
- Keep validation in the Form Request and response fields in the Resource.
- 201 means created; 200 returns the updated record; 204 has no body.
- Deletion is permanent in this example.
- Use new practice tickets: rerunning the seeder can reset or recreate its examples.
- Use editor undo for code mistakes; database reset commands are unnecessary here.
