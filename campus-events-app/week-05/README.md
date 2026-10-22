# Week 05 – Presentation Layer

> Builds on **week 04**. Frontend unchanged. Part of **Milestone 3** 

## Learning goals
- Design **RESTful URLs**: resources (nouns) + HTTP methods (verbs).
- Read path parameters, query parameters and JSON bodies in FlightPHP.
- Answer with the **correct status code**: 200, 201, 400, 404, 500.
- Handle errors **once, globally** instead of `try/catch` in every route.

## Versions (all three run at the same time – compare them in Postman)
| Step | File | Example calls | What it shows |
|------|------|---------------|---------------|
| v1 | `rest/routes/EventRoutesv1.php` | `GET /get_events`, `GET /event_by_id?id=999`, `POST /add_event`, `POST /delete_event?id=3` | verbs in URLs, everything POST/GET, **always 200** (errors hidden inside the JSON) |
| v2 | `rest/routes/EventRoutesv2.php` | `GET /v2/events/999` → 404, `POST /v2/events` → 201/400 | RESTful URLs + status codes, but a **try/catch in every route** |
| v3 (final) | `rest/routes/*Routes.php` + `Flight::map('error')` in `index.php` | `GET /events/999` → 404 … all entities | one line per route, errors handled **once, globally** |

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

## What's new since week 04
| Path | Change |
|------|--------|
| `backend/rest/routes/EventRoutesv1.php`, `EventRoutesv2.php` | **New** – the step-by-step versions |
| `backend/rest/routes/*Routes.php` | **New** – full CRUD for 6 entities + dashboard (ids restricted with `@id:[0-9]+`) |
| `backend/index.php` | Registers all services; loads v1, v2 (under `/v2`) and the final routes; `Flight::map('error')` + `Flight::map('notFound')` return JSON |

## How to run
Run from this folder:
```bash
php -S localhost:8000 server.php
```
The API is then available under `http://localhost:8000/backend`.
If the project is served by Apache (XAMPP/MAMP) instead, `backend/.htaccess` does the routing and the base URL is
`http://localhost/<your-project-folder>/backend`, e.g. with `campus-events-app` copied into `htdocs`:
```
http://localhost/campus-events-app/week-05/backend
```
The `rest/` folder is never part of the URL – all requests go through `backend/index.php`.

### In the browser (GET only)
```
http://localhost:8000/backend/events
http://localhost:8000/backend/events/1
http://localhost:8000/backend/events/999          -> 404
http://localhost:8000/backend/events/9/reviews
http://localhost:8000/backend/registrations?user_id=2
http://localhost:8000/backend/dashboard
http://localhost:8000/backend/v2/events/999       -> v2 demo (404)
http://localhost:8000/backend/event_by_id?id=999  -> v1 demo (200, error inside the JSON)
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

Or with curl:
```bash
curl -i http://localhost:8000/backend/events/999
curl -i -X POST http://localhost:8000/backend/categories -H "Content-Type: application/json" -d '{"name":"Workshops"}'
curl -i -X PUT http://localhost:8000/backend/registrations/1/cancel
curl -i -X DELETE http://localhost:8000/backend/categories/999
```

## Notes
- Anybody can call every endpoint right now (even `DELETE /users/1`!). Authentication comes in week 07,
  authorization in week 08.
