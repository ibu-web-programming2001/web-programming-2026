# Week 06 – OpenAPI Standard (+ External API)

> Builds on **week 05**. Frontend unchanged.
> Completes the backend part of **Milestone 3** (due Mon 23.11.2026): services, routes, OpenAPI, external API.

## Learning goals
- Describe an API with the **OpenAPI 3** standard and show it with **Swagger UI**.
- Document every endpoint with **PHP 8 attributes** (`#[OA\Get(...)]`, swagger-php 6); reuse schemas with `ref`.
- Call a **third-party API** from the backend with cURL – safely:
  timeout, status check, JSON check, friendly error message, app keeps working.

## Versions of the external API call
| Step | Files | Call | What it shows |
|------|-------|------|---------------|
| v1 | `rest/services/OpenMeteoServicev1.php` | `GET /weather/v1/forecast?city=Sarajevo` | `file_get_contents`, **no timeout**, no error handling (unknown city → PHP warning), raw parallel arrays with weather *codes* sent to the client |
| v2 (final) | `rest/services/OpenMeteoService.php` | `GET /weather/forecast?city=Sarajevo&days=7` | cURL with timeouts, status + JSON checks, friendly **503** / 404, one object per day with text + icon |

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

> ⚠️ **Update your `config.php`** – copy the three new `OPEN_METEO_*` methods from `config.example.php`.

## How to run
```bash
cd backend && composer install && cd ..
php -S localhost:8000 server.php
```
- Swagger UI: <http://localhost:8000/backend/public/v1/docs/>
- Raw OpenAPI JSON: <http://localhost:8000/backend/public/v1/docs/swagger.php>

## Lecture demo script
1. Open Swagger UI, *Try it out* on `GET /events/{id}` – the source is `getEventsById()` in `paths/EventPaths.php`.
   Route code (`rest/routes/`) and its documentation (`paths/`) live in separate files – keep them in sync.
2. Show `doc_setup.php`: `EventInput` (what the client sends) vs. `Event` (what the API returns, `allOf`).
3. Call **v1** `GET /weather/v1/forecast?city=Sarajevo` (parallel arrays, `weather_code: 61` – what does 61 mean?),
   then the final `GET /weather/forecast?city=Sarajevo` – our backend reshapes the response into one object per day
   with `"description": "Light rain", "icon": "cloud-rain"`.
4. Two API calls behind one endpoint: geocoding (city → coordinates) and forecast (coordinates → weather).
   `GET /weather/forecast?city=Xqzzyy` → **404** in v2; in v1 → PHP warning + 500.
5. `GET /events/1/weather` (in a few days) vs. `GET /events/11/weather` (more than 16 days ahead → `available: false`)
   vs. `GET /events/9/weather` (event is over).
6. **Failure demo:** set `OPEN_METEO_GEOCODING_URL` to `https://open-meteo.invalid` (or turn Wi-Fi off) →
   503 *"The weather service is not available..."*, while `/events` still works. Then set it to
   `http://10.255.255.1` (an address that never answers) → 503 after the 3-second connect timeout instead of hanging.

## Notes
- swagger-php 6 reads **attributes on classes/methods** only (found by reflection, so `swagger.php` `require_once`s the
  files first). Old tutorials show `/** @OA\Get(...) */` doc blocks and `Generator::scan()` – that is swagger-php 3/4
  and does not work with version 6.
- Open-Meteo needs no API key. If your API needs a key (OpenWeatherMap, TMDB...), add `Config::WEATHER_API_KEY()`
  the same way and **never commit it**.
- Open-Meteo is free for non-commercial use (fair use, < 10 000 calls/day) – fine for the course project.
- Failures are written with `error_log()` for now; week 07 adds a real error log file.
