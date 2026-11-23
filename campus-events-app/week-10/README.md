# Week 10 – Ajax

> Builds on **week 09**. Backend unchanged – this week the **frontend is connected**.
> Part of **Milestone 4**: *"every entity manageable through the UI via AJAX, no static data"*.

## Learning goals
- Send asynchronous HTTP requests with jQuery `$.ajax` – JSON in, JSON out, no page reload.
- Keep the **JWT** in `localStorage` and send it as `Authorization: Bearer ...` with every request.
- React to errors: **401 → login page**, **403 → message**, 400 → show the validation message.
- Render data into the DOM (cards, DataTables, Highcharts) and refresh it after every change.
- Adapt the UI to the role (admin buttons/pages) – remembering that **the backend is the real guard**.

## Versions of an AJAX call – `frontend/ajax-examples.html`
Open <http://localhost:8000/frontend/ajax-examples.html> – or `http://localhost/<your-project-folder>/frontend/ajax-examples.html` with XAMPP/WAMP (see *How to run*) – after logging in to the app. It shows the same `GET /events` four ways:

| Step | Technique | What it shows |
|------|-----------|---------------|
| v1 | `XMLHttpRequest` | what the browser really does: open, header, onload, manual `JSON.parse` |
| v2 | `fetch()` | modern, Promise-based, no library |
| v3 | `$.ajax` | jQuery – shorter, but URL/token/error handling would be repeated in every call |
| v4 (final) | `RestClient.get("events", ...)` | base URL, token, JSON and 401/403 handling written **once** – used by the whole app |

## What's new since week 09
| Path | Change |
|------|--------|
| `frontend/ajax-examples.html` | **New** – the four AJAX versions side by side |
| `frontend/js/config.js` | **New** – API URL calculated from the page URL, role names |
| `frontend/js/auth.js` | **New** – save/read the token, decode the user from the JWT payload, logout |
| `frontend/js/rest-client.js` | **New** – the only `$.ajax` call: JSON, token header, 401/403 handling |
| `frontend/js/utils.js` | **New** – DataTable helper, forms ↔ objects, modals, dates and times, category banners, badges, weather cards |
| `frontend/js/pages/*-page.js` | **New** – one script per view: loads data, renders it, handles buttons and forms |
| `frontend/js/app.js` | Auth guard (login required, admin-only views), role classes on `<body>`, user chip |
| `frontend/views/*.html` | **All static data removed** – only markup with ids; forms really submit |
| `frontend/index.html` | Loads the new scripts; *Users* menu is `admin-only`; real logout |
| `frontend/css/style.css` | `.admin-only` / `.student-only` helpers, past events faded |

## AJAX in one picture
```
click "Save event"
  └─ EventsPage.save(formData)
       └─ RestClient.post("events", event)             → $.ajax POST /backend/events
            Authorization: Bearer eyJ...                  JSON body
                                                        ← 201 + created event (JSON)
       └─ success callback → toastr, close modal, EventsPage.loadEvents() → GET /events → render()
```

## How to run
Set up once (if not done in an earlier week):
```bash
mysql -u root -p < database/campusevents.sql                       # or phpMyAdmin → Import
cp backend/config/config.example.php backend/config/config.php     # set your DB password / JWT secret
cd backend && composer install && cd ..
```

### Option A – PHP built-in server (`localhost:8000`)
Run from the `week-10` folder:
```bash
php -S localhost:8000 server.php
```
| What | URL |
|------|-----|
| App | <http://localhost:8000/frontend/> |
| AJAX examples | <http://localhost:8000/frontend/ajax-examples.html> |
| API | <http://localhost:8000/backend/> (e.g. <http://localhost:8000/backend/events>) |
| Swagger docs | <http://localhost:8000/backend/public/v1/docs/> |

### Option B – XAMPP / WAMP / MAMP (Apache)
Copy (or symlink) the project into the web root – `htdocs` (XAMPP/MAMP) or `www` (WAMP) – start Apache + MySQL
and open it under `http://localhost/<your-project-folder>/...`. `server.php` is **not** used here –
`backend/.htaccess` routes the requests to FlightPHP instead.

For example, with `campus-events-app` copied into `htdocs`:
| What | URL |
|------|-----|
| App | <http://localhost/campus-events-app/week-10/frontend/> |
| AJAX examples | <http://localhost/campus-events-app/week-10/frontend/ajax-examples.html> |
| API | <http://localhost/campus-events-app/week-10/backend/> (e.g. `.../backend/events`) |
| Swagger docs | <http://localhost/campus-events-app/week-10/backend/public/v1/docs/> |

Apache needs `mod_rewrite` enabled and `AllowOverride All` for the folder (the default in XAMPP), otherwise
every API call except `/backend/` returns 404.

### No base URL to configure
The frontend works out the API URL from the page URL (`frontend/js/config.js`) – everything before
`frontend/` is replaced with `backend/`:
```
http://localhost:8000/frontend/                         -> http://localhost:8000/backend/
http://localhost/campus-events-app/week-10/frontend/    -> http://localhost/campus-events-app/week-10/backend/
```
So the same code runs on both setups (and after deployment) without changes.

### Log in
Log in as `admin@campusevents.ba` / `Admin123!` and as `lana@campusevents.ba` / `Student123!` in a second (private) window.
