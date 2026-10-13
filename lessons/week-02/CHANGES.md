# Week 2 — Completed changes

This version completes the [guide](GUIDE.md), including its Form Request and API Resource extensions. The guide and [command reference](QUICK-REFERENCE.md) remain the original lesson instructions; this file describes the final result.

Tickets are sample PHP arrays. A preview checks input and returns proposed ticket data without saving anything.

## What changed and why

| File | Responsibility |
| --- | --- |
| `routes/api.php` | Connects three ticket routes to controller methods; keeps the hello route |
| `app/Http/Controllers/TicketController.php` | Lists sample tickets, finds one by ID, and produces a preview |
| `app/Http/Requests/PreviewTicketRequest.php` | Allows this public example and validates the title before the preview method runs |
| `app/Http/Resources/TicketResource.php` | Selects response fields and includes an ID only when one exists |
| `README.md` | Points to this week's completed version and setup instructions |

The inline ticket closures from the early steps have been replaced by controller routes. Sample data lives in one method, so the list and detail endpoints use the same values.

No new model, migration, package, or custom error middleware is needed. Laravel's existing API JSON configuration handles framework errors, including validation failures.

## Request flow

```text
GET tickets → route → controller → Resource → JSON
POST preview → route → Form Request validation → controller → Resource → JSON
```

A **Form Request** checks input. An **API Resource** formats output. Neither stores data.

## Run and check

Complete the setup in the [README](../../README.md), then run:

```sh
php artisan serve
```

In Postman, use `http://127.0.0.1:8000` and send `Accept: application/json`.

| Request | Expected result |
| --- | --- |
| `GET /api/hello` | 200, `{"message":"Hello World"}` |
| `GET /api/tickets` | 200, two tickets inside `data` |
| `GET /api/tickets/1` | 200, one ticket inside `data` |
| `GET /api/tickets/99` | 404, `{"message":"Ticket not found"}` |
| `GET /api/tickets/abc` | 404, rejected by the numeric route constraint |
| `GET /api/tickets/preview` | 405, this path requires POST |
| `POST /api/tickets/preview` with a valid title | 200, an unsaved preview inside `data` |
| `POST /api/tickets/preview` with `{}` | 422, validation errors for `title` |

For POST, select **Body → raw → JSON** and send `Content-Type: application/json`:

```json
{"title":"Projector is not working"}
```

Expected response, with status **200 OK**:

```json
{
    "data": {
        "title": "Projector is not working",
        "status": "open"
    }
}
```

The preview has no ID because nothing is created. Request the list again: it still contains the original two tickets. A successful preview returns 200; 201 Created will become relevant when we save new records.

The title must be a nonempty string of at most 100 characters. Try a missing title, a number, and a title of 101 characters: each should return 422. Sending `"status":"closed"` does not override the preview's `open` status.

Resources add the `data` wrapper to successful ticket responses. The hello response, missing-ticket message, and validation errors keep their own shapes.

In a second terminal:

```sh
php artisan route:list --path=api
```

Check the requests in the table using Postman. Automated testing will be introduced in a later lesson. The optional practice tasks in the guide remain exercises.

## Before Week 3

### Review and practise

1. Trace a request from its route through the controller to its response.
2. Explain the difference between a Form Request and an API Resource.
3. Rebuild one endpoint in a separate practice copy using the guide and Artisan generators.
4. Bring one successful request and one validation failure to discuss.

### Read ahead

| Topic | Focus |
| --- | --- |
| Databases and tables | How stored records differ from temporary arrays |
| Primary keys | How an ID identifies a record |
| Migrations | How code describes database structure |
| Eloquent models | How Laravel reads and writes records |
| CRUD and HTTP methods | The purpose of GET, POST, PATCH/PUT, and DELETE |

Skim the database, migration, and Eloquent introductions in the [Laravel documentation](https://laravel.com/docs/13.x). Detailed commands and implementation will be in the next guide.

Next week, we will replace the temporary arrays with stored records. Creating a ticket will save it so a later GET request can retrieve it.

When `week-03-start` is published before class, download or switch to it, read its guide, and complete its setup while online. Bring the working folder with dependencies and local configuration. Keep practice work in a separate copy.
