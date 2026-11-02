# Week 05 – Presentation Layer

> Builds on **week 04**. Frontend unchanged.
> Routes are part of Milestone 3.

## Learning goals
- Design **RESTful URLs**: resources (nouns) + HTTP methods (verbs).
- Read path parameters, query parameters and JSON bodies in FlightPHP.
- Answer with the **correct status code**: 200, 201, 400, 404, 500.
- Handle errors **once, globally** instead of `try/catch` in every route.

## Versions (show them in this order)
| Step | Files | What it shows | Run |
|------|-------|---------------|-----|
| v1 | `rest/routes/EventRoutesv1.php` | verbs in URLs, everything POST/GET, **always 200** (errors hidden inside the JSON) | `GET /event_by_id?id=999` |
| v2 | `rest/routes/EventRoutesv2.php` | RESTful URLs + status codes, but a **try/catch in every route** | `GET /v2/events/999` → 404 |
| v3 (final) | `rest/routes/*Routes.php` + `Flight::map('error')` in `index.php` | one line per route, errors handled **once, globally**, all entities | `GET /events/999` → 404 |

All three run at the same time, so they can be compared side by side in Postman.

## What's new since week 04
| Path | Change |
|------|--------|
| `backend/rest/routes/EventRoutesv1.php`, `EventRoutesv2.php` | **New** – the step-by-step versions |
| `backend/rest/routes/*Routes.php` | **New** – full CRUD for 6 entities + dashboard (ids restricted with `@id:[0-9]+`) |
| `backend/index.php` | Registers all services; loads v1, v2 (under `/v2`) and the final routes; `Flight::map('error')` + `Flight::map('notFound')` return JSON |

## API overview (final version)
| Method | URL | Status | Description |
|--------|-----|--------|-------------|
| GET | `/events`, `/clubs`, `/categories`, `/registrations`, `/reviews`, `/users` | 200 | list |
| GET | `/{resource}/{id}` | 200 / 404 | one item |
| POST | `/{resource}` | 201 / 400 | create |
| PUT | `/{resource}/{id}` | 200 / 400 / 404 | update |
| DELETE | `/{resource}/{id}` | 200 / 400 / 404 | delete |
| GET | `/events/{id}/reviews` | 200 / 404 | nested resource |
| GET | `/registrations?user_id=2` | 200 | filter with a query parameter |
| PUT | `/registrations/{id}/cancel`, `/registrations/{id}/attend` | 200 / 400 | actions on a resource |
| GET | `/dashboard` | 200 | statistics |

## How to run
Start MySQL, then:
1. Import `database/campusevents.sql` in phpMyAdmin.
2. Copy `backend/config/config.example.php` to `backend/config/config.php` and set your database credentials.
3. Install the dependencies from the `backend` folder:
   ```bash
   cd backend
   composer install
   ```

### With XAMPP/WAMP (Apache)
Copy `campus-events-app` into `htdocs` (XAMPP) or `www` (WAMP) and start Apache. The base URL is:
```
http://localhost/<your-project-folder>/backend
```
For example, with `campus-events-app` copied into `htdocs` or `www`:
```
http://localhost/campus-events-app/week-05/backend
```
`backend/.htaccess` sends every request to `backend/index.php`, so the `rest/` folder is never part of the URL.

If every URL except `{base}/` gives an Apache 404, `mod_rewrite` is off. On WAMP, turn it on from the tray icon: *Apache → Apache modules → rewrite_module*.

### With the PHP built-in server
Run from the `week-05` folder:
```bash
php -S localhost:8000 server.php
```
The base URL is:
```
http://localhost:8000/backend
```

### In the browser (GET only)
Add these paths to your base URL (`{base}`):
```
{base}/events
{base}/events/1
{base}/events/999          -> 404
{base}/events/9/reviews
{base}/registrations?user_id=2
{base}/dashboard
{base}/v2/events/999       -> v2 demo (404)
{base}/event_by_id?id=999  -> v1 demo (200, error inside the JSON)
```

### In Postman (all methods)
The **same URL** is used with different methods – only change the method dropdown.
For `POST` and `PUT` set *Body → raw → JSON*.

| Method | URL | Body | Expected |
|--------|-----|------|----------|
| GET | `{base}/events` | – | 200, list |
| GET | `{base}/events/999` | – | 404 |
| POST | `{base}/events` | `{"title":"Intro to Docker Workshop","club_id":2,"category_id":2,"location":"FENS Lab 1","city":"Sarajevo","starts_at":"2026-12-03 17:00","capacity":25}` | 201 |
| POST | `{base}/events` | `{"title":""}` | 400 |
| PUT | `{base}/events/5` | `{"title":"Movie Night: Classic Cinema","club_id":4,"category_id":4,"location":"IBU Amphitheatre","city":"Sarajevo","starts_at":"2026-10-14 20:00","capacity":200}` | 200 |
| DELETE | `{base}/events/1` | – | 400 (has registrations) |
| POST | `{base}/registrations` | `{"user_id":3,"event_id":12}` | 201 |
| PUT | `{base}/registrations/1/cancel` | – | 200 |
| PUT | `{base}/registrations/4/attend` | – | 200 |
| POST | `{base}/categories` | `{"name":"Workshops"}` | 201 |
| GET | `{base}/something` | – | 404 JSON |
| POST | `{base}/add_event` | `{"title":""}` | v1 demo: 200 with error in JSON |
| POST | `{base}/v2/events` | `{"title":""}` | v2 demo: 400 |

Re-import `database/campusevents.sql` afterwards to reset the sample data.
Expected error responses (shortened):
```
GET  {base}/events/999        404  {"error":"Event not found."}
POST {base}/events            400  {"error":"Field 'title' is required."}
GET  {base}/something         404  {"error":"Endpoint not found."}
GET  {base}/event_by_id?id=999  200  {"success":false,"error":"Event not found."}
```

## Notes
- Anybody can call every endpoint right now (even `DELETE /users/1`!). Authentication comes in week 07,
  authorization in week 08.
