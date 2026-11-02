# Week 06 – OpenAPI Standard (+ External API)

> Builds on **week 05**. Frontend unchanged.
> Completes the backend part of **Milestone 3**: services, routes, OpenAPI, external API.

## Learning goals
- Describe an API with the **OpenAPI 3** standard and show it with **Swagger UI**.
- Document every endpoint with **PHP 8 attributes** (`#[OA\Get(...)]`, swagger-php 6); reuse schemas with `ref`.
- Call a **third-party API** from the backend with cURL – safely:
  timeout, status check, JSON check, friendly error message, app keeps working.

## Versions (show them in this order)
| Step | Files | What it shows | Run |
|------|-------|---------------|-----|
| v1 | `rest/services/OpenMeteoServicev1.php` | `file_get_contents`, **no timeout**, no error handling (unknown city → PHP warning), raw parallel arrays with weather *codes* sent to the client | `GET /weather/v1/forecast?city=Sarajevo` |
| v2 (final) | `rest/services/OpenMeteoService.php` | cURL with timeouts, status + JSON checks, friendly **503** / 404, one object per day with text + icon | `GET /weather/forecast?city=Sarajevo&days=7` |

## What's new since week 05
| Path | Change |
|------|--------|
| `backend/composer.json` | + `zircote/swagger-php` (^6.12 – reads PHP 8 attributes, needs PHP ≥ 8.2) |
| `backend/public/v1/docs/index.html` | **New** – Swagger UI |
| `backend/public/v1/docs/swagger.php` | **New** – loads `doc_setup.php` + `paths/*.php`, `(new Generator())->generate()`, outputs the OpenAPI JSON |
| `backend/public/v1/docs/doc_setup.php` | **New** – attributes on an empty class: API info, server URL, reusable schemas (`Event`, `EventInput`, `Forecast`, `Error`...) |
| `backend/public/v1/docs/paths/*Paths.php` | **New** – one class per route file (`EventPaths` ↔ `EventRoutes.php`), one empty method per endpoint carrying `#[OA\Get/Post/Put/Delete]` |
| `backend/config/config.example.php` | + `OPEN_METEO_GEOCODING_URL`, `OPEN_METEO_FORECAST_URL`, `OPEN_METEO_TIMEOUT` |
| `backend/rest/services/OpenMeteoServicev1.php` | **New** – the naive version (+ route `GET /weather/v1/forecast`) |
| `backend/rest/services/OpenMeteoService.php` | **New** – `geocode()`, `forecast()`, `forecast_for_day()`, one `request()` with all error handling |
| `backend/rest/services/EventService.php` | + `get_weather()` – event city + date → OpenMeteoService (a service using another service) |
| `backend/rest/routes/WeatherRoutes.php` | **New** – `GET /weather/forecast?city=&days=` |
| `backend/rest/routes/EventRoutes.php` | + `GET /events/{id}/weather` |
| `docs/api.http` | **New** – manual requests for VS Code (REST Client) or PhpStorm; change `@base` when using XAMPP/WAMP |

> ⚠️ **Update your `config.php`** – copy the three new `OPEN_METEO_*` methods from `config.example.php`.

## How to run
Start MySQL, import `database/campusevents.sql`, then install the dependencies (PHP 8.2 or newer is required):
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
http://localhost/campus-events-app/week-06/backend
```
If every URL except `{base}/` gives an Apache 404, `mod_rewrite` is off. On WAMP, turn it on from the tray icon: *Apache → Apache modules → rewrite_module*.

### With the PHP built-in server
Run from the `week-06` folder:
```bash
php -S localhost:8000 server.php
```
The base URL is:
```
http://localhost:8000/backend
```

### In the browser
Add these paths to your base URL (`{base}`):
```
{base}/public/v1/docs/                      -> Swagger UI
{base}/public/v1/docs/swagger.php           -> raw OpenAPI JSON
{base}/weather/v1/forecast?city=Sarajevo    -> v1 (parallel arrays, weather codes)
{base}/weather/forecast?city=Sarajevo&days=7
{base}/weather/forecast?city=Xqzzyy         -> 404
{base}/events/1/weather
```
Expected output (shortened):
```
GET {base}/weather/forecast?city=Sarajevo&days=1  200  {"city":"Sarajevo","country":"Bosnia and Herzegovina",...,"days":[{"date":"...","weather_code":61,"description":"Light rain","icon":"cloud-rain",...}]}
GET {base}/weather/forecast?city=Xqzzyy           404  {"error":"No city called 'Xqzzyy' was found."}
```
The weather values change from day to day. Swagger UI and the weather endpoints need an internet connection.