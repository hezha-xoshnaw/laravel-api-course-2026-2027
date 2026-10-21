# Week 4 — Update and delete tickets

**Duration:** approximately 2 hours. Start from `week-04-start`. Its application code is the completed Week 3 version. Implement the steps below during the lesson.

We can already create tickets and read them from SQLite. This week, we will change an existing ticket's title or status and delete a ticket. Together, these operations complete CRUD: create, read, update, and delete.

## Learning objectives

- Explain partial updates and the purpose of PATCH.
- Validate only the fields supplied in an update.
- Use route model binding and Eloquent to update or delete one record.
- Return an updated Resource with 200 or an empty deletion response with 204.
- Check that invalid input and missing records produce appropriate errors.

We will use the existing tickets table. No new migration, model, package, or seeder is required. Login, relationships, soft deletes, and automated tests will come in later lessons.

## 1. Start and review

Follow the [README](../../README.md) if you need initial setup. With an existing working copy:

```sh
php artisan migrate:status
php artisan route:list --path=api
php artisan serve
```

Use a second terminal for commands while the server runs. In Postman, use `http://127.0.0.1:8000` and send `Accept: application/json`.

Create a ticket for today's practice:

```http
POST /api/tickets
Accept: application/json
Content-Type: application/json
```

```json
{"title":"Classroom projector needs checking"}
```

Expect **201 Created**. Copy its returned ID and retrieve it with `GET /api/tickets/{id}`. Use that actual ID wherever this guide shows `{id}`; do not type the braces into Postman.

Keep this practice record separate from the two seeded examples. Rerunning the seeder can reset sample statuses, and deleting a seeded example followed by seeding can recreate it.

## 2. Understand the HTTP operations

| Method | Purpose in our API | Success |
| --- | --- | --- |
| POST | Create a ticket | 201 with the new ticket |
| GET | Retrieve tickets | 200 with data |
| PATCH | Change selected fields of one ticket | 200 with the updated ticket |
| DELETE | Remove one ticket | 204 with no response body |

PUT describes replacing the resource representation. PATCH describes applying changes. Today we implement PATCH: sending only `status` must leave the title unchanged. We will not register a PUT endpoint in this lesson.

An omitted field and an empty field are different. Omitting `title` means leave it as it is. Sending `"title":""` means supplying an invalid title and should return 422.

## 3. Generate the update Form Request

```sh
php artisan make:request UpdateTicketRequest
```

Replace `app/Http/Requests/UpdateTicketRequest.php` with:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:100'],
            'status' => ['sometimes', 'required', 'string', 'in:open,closed'],
        ];
    }
}
```

`authorize()` stays `true` for this public classroom example. It is not an ownership check or a login system.

| Rule | Meaning |
| --- | --- |
| `sometimes` | Validate this field only if it was supplied |
| `required` | If supplied, it cannot be empty or null |
| `string` | The value must be text |
| `max:100` | Limit the title to 100 characters |
| `in:open,closed` | Allow only the listed statuses |

Combining `sometimes` and `required` lets the client omit a field but rejects an empty value when it is present.

We keep separate creation and update requests because their rules differ. Creation requires a title and always uses the database's default status. Update accepts either title, status, or both.

For this simple lesson, `{}` is accepted as a no-op: the response returns the ticket unchanged with 200. Input containing only unrecognized fields has the same effect. Rejecting requests without any editable fields is a possible later improvement.

## 4. Allow status updates on the model

Open `app/Models/Ticket.php`. Change only its Fillable attribute:

```php
#[Fillable(['title', 'status'])]
```

Keep its namespace, imports, and class. This permits both fields when Eloquent receives an array through `update()`.

Validation and fillable have different jobs: the Form Request checks values, and the model controls which attributes can be mass assigned. Do not remove the Fillable restriction or switch to an unguarded model.

`StoreTicketRequest` still validates only `title`. Since creation uses `$request->validated()`, sending `status` in a POST still cannot override the creation default. We have expanded the model's permitted fields, while each operation's request decides which input to accept.

## 5. Add the controller update method

Open `app/Http/Controllers/TicketController.php`. Add this import beside the existing request import:

```php
use App\Http\Requests\UpdateTicketRequest;
```

Keep `StoreTicketRequest`, `TicketResource`, and `Ticket` imported. Add this public method **inside the controller class**, after `store()` and before the class's final closing brace:

```php
public function update(UpdateTicketRequest $request, Ticket $ticket)
{
    $ticket->update($request->validated());

    return new TicketResource($ticket);
}
```

Laravel resolves the existing ticket and validates input before entering the method. `update()` applies only the accepted fields and saves them. Eloquent also maintains `updated_at`, though our Resource does not expose timestamps.

Do not supply default values for missing input, such as setting an omitted title to null. The validated array already contains only supplied, accepted fields.

The Resource returns the ticket with the usual `data` wrapper and status **200 OK**. The existing `TicketResource` already exposes all the fields we need.

## 6. Register the PATCH route

In `routes/api.php`, keep all existing routes and append:

```php
Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])
    ->whereNumber('ticket');
```

`{ticket}` matches `$ticket` in the controller's `Ticket $ticket` argument. The existing route model binding retrieves the record or returns 404.

```sh
php artisan route:list --path=api
```

Confirm that PATCH appears with `TicketController@update`. Artisan generates the Form Request file; it does not write this route or method for you.

## 7. Check partial updates in Postman

Send `PATCH /api/tickets/{id}` for the practice ticket created earlier. Choose **Body → raw → JSON** and include `Content-Type: application/json`.

First, send only the status:

```json
{"status":"closed"}
```

Expect **200 OK**. The response should show `closed` and preserve the original title:

```json
{
    "data": {
        "id": 3,
        "title": "Classroom projector needs checking",
        "status": "closed"
    }
}
```

The actual ID may differ. Send a GET for that ID to confirm the update was stored.

Next, change only the title:

```json
{"title":"Projector has been repaired"}
```

Expect 200. The status must remain `closed`. You can also send both fields in one PATCH:

```json
{"title":"Projector needs another check","status":"open"}
```

### Check invalid input

| JSON body | Expected |
| --- | --- |
| `{"status":"pending"}` | 422, invalid status |
| `{"status":null}` | 422, empty status |
| `{"title":""}` | 422, empty title |
| `{"title":123}` | 422, title must be text |
| Title with 101 characters | 422, title is too long |
| `{"title":"This must not be saved","status":"pending"}` | 422; neither field changes |
| `{}` | 200, no changes |

Laravel returns validation errors with `message` and `errors`. Request the record again after invalid input to confirm that nothing changed. Use an existing ID for these checks; a missing record is rejected by model binding first.

Try sending an extra `id` field along with a valid title. The update must keep the original ID because it is not part of the validated input or the model's fillable list.

## 8. Add deletion

Inside `TicketController`, add another public method:

```php
public function destroy(Ticket $ticket)
{
    $ticket->delete();

    return response()->noContent();
}
```

`destroy` is the conventional controller action name. Calling `delete()` on the resolved model removes that one record. This is permanent deletion; our model has no soft-delete feature.

`noContent()` returns **204 No Content**. Do not add a success JSON message or a Resource to a 204 response: its body is empty.

Append the route in `routes/api.php`:

```php
Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])
    ->whereNumber('ticket');
```

Run `php artisan route:list --path=api` again. DELETE should map to `TicketController@destroy`.

## 9. Check deletion and the full flow

1. Send `DELETE /api/tickets/{id}` for your practice ticket. It needs no request body. Expect 204 with an empty response body.
2. Send GET for that ID. Expect 404.
3. Request the list. That ticket should be absent.
4. Repeat DELETE for the same ID. Expect 404 because route model binding no longer finds it.

Repeating DELETE leaves the record absent. Its second response can be 404 even though the first response was 204.

Complete this short scenario with another fresh practice ticket:

```text
POST → copy returned ID → GET → PATCH → GET → DELETE → GET returns 404
```

| Endpoint | Final expected result |
| --- | --- |
| `GET /api/hello` | Original 200 greeting |
| `GET /api/tickets` | 200, current list |
| `GET /api/tickets/{id}` | 200 for an existing record, otherwise 404 |
| `POST /api/tickets` | 201 for valid creation, otherwise 422 |
| `PATCH /api/tickets/{id}` | 200 for a valid update; 422 for invalid input on an existing record |
| `DELETE /api/tickets/{id}` | 204 for an existing record |
| PATCH or DELETE for a missing or nonnumeric ID | 404 |
| `PUT /api/tickets/{id}` for a numeric ID | 405; PUT was not registered |

Our API continues to accept requests without login. Use classroom sample data.

## 10. Common mistakes and recovery

| Problem | What to check |
| --- | --- |
| PATCH returns 405 | Check the method, route, and `route:list` output |
| Update returns 403 | Check `UpdateTicketRequest::authorize()` |
| Status does not change | Check both the request rules and the model's Fillable attribute |
| Omitting the title returns 422 | Include `sometimes` in the update title rules |
| Omitted fields are erased | Pass only validated input; remove fallback values for absent fields |
| Missing ticket returns 404 before validation | Expected: route model binding resolves the record first |
| Status resets after seeding | TicketSeeder resets matching sample statuses; use a separate practice record |
| Deleted sample comes back after seeding | The seeder recreates absent sample titles |
| No JSON appears after DELETE | Expected for 204; inspect the status and then GET the record |

Use editor undo for accidental edits. To remove the update feature from a practice copy, remove its route and controller method/import before deleting `UpdateTicketRequest.php`. Restore the original model Fillable attribute if status updates are no longer needed.

There is no schema change to roll back this week. Avoid resetting the database just to correct a controller or route mistake. To continue practising after deleting a ticket, create another one with POST.

## 11. Practice and review

Choose one or two tasks:

1. Close and reopen a ticket using PATCH. Explain why its title stays unchanged.
2. Send a valid title with an invalid status and verify that neither field is saved.
3. Compare empty input, a missing title, and an explicitly empty title.
4. Delete a practice ticket and explain why a later GET and DELETE return 404.
5. Check that POST still defaults to `open` even if the client sends `"status":"closed"`.

Be ready to explain the difference between 200, 201, 204, 404, and 422 using one request from this lesson for each.

### Optional reading: resource routes

`Route::apiResource(...)` can register conventional routes for CRUD controller methods. Our method names already follow those conventions. Its update route accepts both PUT and PATCH, so switching to it requires deciding how your API handles both methods. Keep the explicit PATCH route for today's implementation.

## Suggested timing

| Minutes | Activity |
| --- | --- |
| 0–15 | Review and create a practice ticket |
| 15–65 | PATCH rules, Fillable, controller, and route |
| 65–90 | Deletion and 204 responses |
| 90–115 | Full Postman scenario and invalid input |
| 115–120 | Recap and home practice |

## References

- [Laravel validation](https://laravel.com/docs/13.x/validation) — Form Requests and conditional validation.
- [Laravel Eloquent](https://laravel.com/docs/13.x/eloquent) — updates, mass assignment, and deletion.
- [Laravel controllers](https://laravel.com/docs/13.x/controllers) — conventional actions and API resource routes.

Online references need internet access. Use [QUICK-REFERENCE.md](QUICK-REFERENCE.md) while practising.
