# AGENTS.md – context for AI coding assistants

> This file tells AI tools (Claude, Copilot, Cursor, ChatGPT...) how this project is built.
> Read it completely before suggesting or writing code. Keep it up to date in every milestone.

## Project
**CampusEvents** – a single-page application for student events (IT 2001 Web Programming lecture project).
Students browse campus events, register (limited seats) and write reviews. Admins manage events, clubs,
categories, registrations and users. The weather for an event day comes from the Open-Meteo API.

## Technology stack
- **Frontend:** HTML5, CSS3, JavaScript, jQuery 3, Bootstrap 5, spapp (hash-based SPA router),
  DataTables, Select2, Toastr, Highcharts. No frameworks (React/Vue/Angular are NOT allowed).
- **Backend:** PHP 8.2+, FlightPHP 3, PDO (MySQL). No other PHP framework.
- **Database:** MySQL 8.

## Folder structure
```
frontend/            static SPA – only HTML, CSS and JS
  index.html         app shell, every <section> in <main id="spapp"> is one view
  views/             HTML templates loaded by spapp
  css/  js/  assets/
backend/             REST API – only PHP
  index.php          FlightPHP entry point
  config/            config.example.php (committed), config.php (NOT committed)
  rest/routes/       presentation layer – FlightPHP routes
  rest/services/     business logic layer
  rest/dao/          data access layer (PDO)
  utils/             exceptions, Validator, Roles
  middleware/        reusable FlightPHP middleware
  logs/              info.log / error.log (NOT committed)
docs/                ERD, AI log
database/campusevents.sql  schema + sample data
```

## Architecture rules (backend)
- **routes → services → dao**. A layer may only call the layer directly below it.
- Routes never contain SQL or business rules. DAOs never contain rules or HTTP code.
- **Services are the only layer that calls DAOs.** All validation and rules live in services.
- Every SQL query uses prepared statements. Writable columns are whitelisted in the DAO constructor.
- Services signal problems by throwing exceptions from `utils/exceptions.php`
  (`ValidationException` 400, `NotFoundException` 404, ...). Never `echo` or `exit` in a service.
- Services receive their DAOs through the constructor (`$dao = null` → default DAO) so they can be unit-tested.
- Changes to several tables in one action must use a transaction (`begin_transaction/commit/rollback`).

## Rules the AI must follow
1. Never mix backend and frontend code. No PHP in `frontend/`, no HTML output from `backend/`.
2. Never commit or print secrets. Configuration values come from `Config::...()` only.
3. Keep the existing code style (see naming conventions). Do not introduce new libraries without asking.
4. Explain every change so that the student can defend it without AI help.

## Naming conventions
- PHP classes: `PascalCase` (`EventService`), PHP methods and variables: `snake_case` (`get_by_id`).
- JS objects: `PascalCase` (`EventService`), JS functions and variables: `camelCase`.
- Database tables: plural `snake_case` (`events`, `registrations`), columns `snake_case`.
- View files: `kebab-case.html` (`event-details.html`); view id = file name without `.html`.
