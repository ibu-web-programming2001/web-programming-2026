# Week 04 – Business Logic Layer and Services

> Builds on **week 03**. Frontend unchanged.
> Services are part of Milestone 3.

## Learning goals
- Put **all application rules** in one layer – the services.
- Validate input on the server (never trust the client).
- Signal problems with **exceptions** that carry an HTTP status code.
- Keep services testable: DAOs are passed into the constructor (*dependency injection*).

## Versions (show them in this order)
| Step | Files | What it shows | Run |
|------|-------|---------------|-----|
| v1 | `rest/services/EventServicev1.php` | pass-through to the DAO, **no rules** → empty title, event in 2020, client sets the free seats; unknown club = raw PDOException | `php rest/services/testv1.php` |
| v2 | `rest/services/EventServicev2.php` | rules written inline, errors returned as `['success' => false, 'error' => ...]` – every caller must check, no idea which HTTP status | `php rest/services/testv2.php` |
| v3 (final) | `rest/services/BaseService.php` + one service per entity, `utils/Validator.php`, `utils/exceptions.php`, `utils/Roles.php` | reusable validation, **exceptions with HTTP status**, `BaseService` with shared CRUD, DAOs injected through the constructor | `php rest/services/testv3.php` |

Run from the `backend` folder. Still **no routes** – they come in week 05; the services are tried out from the scripts.

## What's new since week 03
| Path | Change |
|------|--------|
| `backend/utils/exceptions.php` | **New** – `HttpException` + `ValidationException` (400), `UnauthorizedException` (401), `ForbiddenException` (403), `NotFoundException` (404), `ExternalServiceException` (503) |
| `backend/utils/Validator.php` | **New** – `required`, `length`, `email`, `integer`, `one_of`, `date`, `datetime`, `password` |
| `backend/utils/Roles.php` | **New** – `admin` / `student` constants |
| `backend/rest/services/BaseService.php` | **New** – shared CRUD + `validate()` hook + 404 handling |
| `backend/rest/services/{Category,Club,Event,Registration,Review,User}Service.php` | **New** – one service per entity with its rules |
| `backend/rest/services/DashboardService.php` | **New** – statistics that combine several DAOs |
| `backend/rest/services/EventServicev1.php`, `EventServicev2.php`, `testv1-3.php` | **New** – the step-by-step versions |

## Business rules implemented
| Service | Rules |
|---------|-------|
| Category | name required (2–60), **unique name**; cannot delete a category that has events |
| Club | name required, **unique name**; founded year not in the future; cannot delete a club that has events |
| Event | title/club/category/location/start/capacity required; club & category must exist; `starts_at` is a valid date-time (HTML `2026-11-20T18:00` is accepted and normalised); a **new event must be in the future**; same title at the same time = duplicate; new event → `available_seats = capacity`; capacity cannot drop below taken seats; cannot delete an event with registrations |
| Registration | event must not have started; **free seat** needed; **max 3 upcoming registrations** per student; no duplicate registration; register / cancel / delete run in a **transaction** (registrations + events); only the status can be changed (`cancelled` or `attended`) |
| Review | rating 1–5; **one review per student per event**; only rating/comment can be edited |
| User | names, valid & unique email, valid role, faculty one of FENS/FASS/FBA/FEDU; strong password; password stored as **bcrypt hash**; empty password on update = keep the old one |

## How to run
From the terminal (run from the `backend` folder):
```bash
cd backend
php rest/services/testv1.php     # then testv2.php, testv3.php
```
Or, if the project is served by Apache (XAMPP/MAMP), open the script in the browser:
```
http://localhost/<your-project-folder>/backend/rest/services/testv1.php
```
For example, with `campus-events-app` copied into `htdocs`:
```
http://localhost/campus-events-app/week-04/backend/rest/services/testv1.php
http://localhost/campus-events-app/week-04/backend/rest/services/testv2.php
http://localhost/campus-events-app/week-04/backend/rest/services/testv3.php
```
Re-import `database/campusevents.sql` afterwards to reset the sample data.
Expected output (shortened):
```
Category without a name................................ ValidationException [400]: Field 'name' is required.
Ema (3 upcoming registrations) registers again......... ValidationException [400]: A student can have at most 3 upcoming registrations. ...
Tarik registers for sold-out Robot Build Day........... ValidationException [400]: 'Robot Build Day' is sold out.
```
