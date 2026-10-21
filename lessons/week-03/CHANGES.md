# Week 3 — Completed changes

Tickets now persist in SQLite. The controller uses Eloquent to list, find, and create records instead of reading a static array.

The original [guide](GUIDE.md) and [command reference](QUICK-REFERENCE.md) remain unchanged so you can repeat the lesson from `week-03-start`.

## What changed and why

| File | Change |
| --- | --- |
| `database/migrations/2026_10_21_000000_create_tickets_table.php` | Creates the tickets table with an ID, title, default status, and timestamps |
| `app/Models/Ticket.php` | Represents stored tickets; allows mass assignment of the title |
| `database/seeders/TicketSeeder.php` | Adds two sample tickets, matching existing sample titles on later runs |
| `database/seeders/DatabaseSeeder.php` | Calls TicketSeeder instead of creating a starter user |
| `app/Http/Requests/StoreTicketRequest.php` | Validates creation input; replaces PreviewTicketRequest |
| `app/Http/Resources/TicketResource.php` | Reads model properties and returns only ID, title, and status |
| `app/Http/Controllers/TicketController.php` | Uses Eloquent for listing, route model binding for detail, and creation for POST |
| `routes/api.php` | Uses `{ticket}` for model binding and replaces the preview route with `POST /api/tickets` |
| `README.md` | Updates setup and links for the completed version |

No new dependencies or automated tests were added. The hello route remains unchanged.

## How the pieces work together

```text
GET → route → controller → Eloquent query → SQLite → Resource → JSON
POST → route → Form Request → controller → Eloquent insert → SQLite → Resource → JSON
```

The migration defines structure. The seeder supplies classroom data. The model performs database operations. The Form Request validates input, and the Resource selects public output fields.

`{ticket}` matches the controller argument `Ticket $ticket`. Laravel looks up that record and returns 404 if it is missing. We no longer need a manual search loop.

The client supplies a title. SQLite assigns the ID and defaults status to `open`. After insertion, `refresh()` reads database defaults before the Resource builds the response. Successful creation returns **201 Created**.

## Run and check

For a new installation, follow the [README](../../README.md). For an existing configured copy, run:

```sh
php artisan migrate
php artisan db:seed
php artisan route:list --path=api
php artisan serve
```

Use SQLite in your local `.env`. The default database file is `database/database.sqlite`; remove an old active `DB_DATABASE` override if it points elsewhere, then run `php artisan config:clear`.

Seeding does not clear the database. It inserts the two samples if their titles are absent and resets their statuses if present. It leaves other tickets and existing users in place. Sample titles are not unique database keys.

In Postman, use `http://127.0.0.1:8000` and send `Accept: application/json`.

| Request | Expected result |
| --- | --- |
| `GET /api/hello` | 200, original greeting |
| `GET /api/tickets` | 200, stored tickets inside `data` |
| `GET /api/tickets/{id}` with an ID from the list | 200, that ticket inside `data` |
| GET with a missing or nonnumeric ID | 404 |
| `POST /api/tickets` with a valid title | 201, the saved ticket |
| POST with a missing, numeric, or overlong title | 422, validation errors |
| `POST /api/tickets/preview` | 404, the preview route was removed |

For creation, select **Body → raw → JSON**, send `Content-Type: application/json`, and use:

```json
{"title":"Projector is not working"}
```

Example response:

```json
{
    "data": {
        "id": 3,
        "title": "Projector is not working",
        "status": "open"
    }
}
```

The ID may differ. Retrieve that ID and request the list again: the new record should appear. Restart the server and retrieve it again to see that data survives between runs.

Sending an extra `"status":"closed"` does not change the creation default. The controller uses validated input and the model permits mass assignment only for the title. Repeating a valid POST creates another record; duplicate titles are allowed. Invalid input should not insert a record.

## Before the next lesson

- Review migrations versus seeders, and Form Requests versus Resources.
- Explain why creation returns 201 while a GET returns 200.
- Practise retrieving a newly created ticket by its returned ID.
- Read about PUT/PATCH, DELETE, and the difference between replacing a record and changing selected fields.
- Review Eloquent's update and delete operations and the purpose of a 204 No Content response.

The proposed next step is updating and deleting stored tickets. Detailed implementation will belong in the next week's starting guide. Keep practice work separately and bring the published start version with its dependencies and local setup ready.
