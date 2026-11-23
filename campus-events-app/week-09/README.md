# Week 09 – API Testing

> Builds on **week 08**. Frontend unchanged (connected next week!).

## Learning goals
- Know the test pyramid: many fast **unit tests**, fewer slower **API/integration tests**, a few manual/UI tests.
- Write PHPUnit tests in the **Arrange – Act – Assert** style.
- Replace DAOs with **mocks** to test business rules without a database.
- Test the API from the outside with real HTTP requests: status codes, JSON, 401/403 rules.

## Versions
| Step | File | What it shows |
|------|------|---------------|
| v1 | `tests/curl_testv1.php` | calls the API and **prints** the responses – a human must read and judge them; nothing fails automatically |
| v2 (final) | `tests/unit/*Test.php`, `tests/api/*Test.php` | PHPUnit **assertions** – green/red, repeatable, runs in CI |
| v2 (Postman) | `docs/postman/CampusEvents.postman_collection.json` | the same idea in Postman: `pm.test(...)` scripts after every request – **this is how the final exam is graded** |

## What's new since week 08
| Path | Change |
|------|--------|
| `backend/composer.json` | + `require-dev: phpunit/phpunit`, scripts `composer test`, `composer test:api` |
| `backend/phpunit.xml` | **New** – two test suites: `unit` and `api` |
| `backend/tests/curl_testv1.php` | **New** – version 1 (manual testing) |
| `backend/tests/bootstrap.php` | **New** – autoload; creates `config.php` from the example when missing (CI) |
| `backend/tests/unit/ValidatorTest.php` | **New** – pure functions, no mocks |
| `backend/tests/unit/EventServiceTest.php` | **New** – mocks for 4 DAOs (+ the weather service); seats, dates, capacity, delete rules |
| `backend/tests/unit/RegistrationServiceTest.php` | **New** – max. 3 upcoming, sold out, past event, **rollback**, ownership |
| `backend/tests/unit/AuthServiceTest.php` | **New** – login → decode the JWT, wrong password, 2FA step, register always *student* |
| `backend/tests/unit/TotpTest.php` | **New** – RFC 6238 test vector, time window |
| `docs/postman/*.json` | **New** – Postman collection + environment with `pm.test` scripts |
| `backend/tests/api/ApiTestCase.php` | **New** – cURL helper, login once, skip when the server is down |
| `backend/tests/api/*ApiTest.php` | **New** – 401/403/404/400, full CRUD cycle, register → cancel |

## How to run
```bash
cd backend && composer install

# Unit tests – no database or server needed
composer test                     # = vendor/bin/phpunit --testsuite unit   -> OK (34 tests)

# API tests – need MySQL with campusevents.sql and the running server
cd .. && php -S localhost:8000 server.php      # terminal 1
cd backend && composer test:api                # terminal 2   -> OK (15 tests)
```
Run one test: `vendor/bin/phpunit --filter test_a_student_cannot_have_more_than_three_upcoming_registrations`

### Base URL: PHP built-in server vs. XAMPP / WAMP
The API tests, the manual scripts and Postman need to know **where the API runs**:

| Setup | How to start | Base URL |
|-------|--------------|----------|
| PHP built-in server | `php -S localhost:8000 server.php` in the week folder | `http://localhost:8000/backend` *(default)* |
| XAMPP (Windows/macOS) | Start Apache + MySQL, project in `htdocs/` | `http://localhost/campus-events-app/week-09/backend` |
| WAMP (Windows) | Start WampServer, project in `www/` | `http://localhost/campus-events-app/week-09/backend` |
| MAMP (macOS) | Start servers, project in `htdocs/` | `http://localhost:8888/campus-events-app/week-09/backend` |

Adjust the path to match **your** folder name inside `htdocs/`/`www/`. Open the URL in the browser first – you should see JSON, not a 404.
With Apache you don't need `server.php`; `backend/.htaccess` does the routing (make sure `mod_rewrite` is enabled – it is by default in XAMPP; in WAMP: tray icon → Apache → Apache modules → `rewrite_module`).

Where to change the URL:
| Tool | File / place |
|------|--------------|
| PHPUnit API tests | `backend/phpunit.xml` → `<env name="API_BASE_URL" value="..."/>` |
| `tests/curl_testv1.php` | pass it as an argument: `php tests/curl_testv1.php http://localhost/campus-events-app/week-09/backend` |
| REST Client | `docs/api.http` → `@base = ...` (an XAMPP/WAMP line is already there, commented out) |
| Postman | environment *CampusEvents – local* → variable `baseUrl` |
| newman | add `--env-var "baseUrl=http://localhost/campus-events-app/week-09/backend"` |

## Postman tests (exam style)
Import `docs/postman/CampusEvents.postman_collection.json` and `CampusEvents.postman_environment.json` into Postman
(*File → Import*), select the environment *CampusEvents – local* and click **Run collection**.
- The login request **saves the token** in a collection variable (`pm.collectionVariables.set`) – every later request sends it.
- Typical checks: status code, JSON, array length, required properties, specific values, error messages.
- `pm.expect(Number(event.average_rating)).to.eql(4.5)` – MySQL `DECIMAL` values (AVG, ROUND) arrive as **strings** in JSON.

Command line (same tests, e.g. in CI):
```bash
npx newman run docs/postman/CampusEvents.postman_collection.json -e docs/postman/CampusEvents.postman_environment.json
#   requests 14 · assertions 30 · failed 0
```
