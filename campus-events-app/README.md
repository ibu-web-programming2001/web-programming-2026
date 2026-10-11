# CampusEvents – lecture code, week by week

IT 2001 Web Programming · IBU · 2026/27

CampusEvents is the application we build together in the lectures: students discover campus events
(concerts, hackathons, sports, workshops), register for them (limited seats, max. 3 upcoming registrations),
cancel, and write reviews. Admins manage events, clubs, categories, registrations (check-in) and users.
The external API is **Open-Meteo** (weather planner + weather on the day of each event).

Every folder is a **complete, runnable snapshot**: `week-N` = `week-(N-1)` + what we covered in week N.
Inside a week, the step-by-step versions sit next to the final code (`EventDaov1.php` → `v2` → `v3` → `EventDao.php`,
`EventServicev1/v2`, `EventRoutesv1/v2`, `OpenMeteoServicev1`, `AuthDemoRoutesv1`, `RoleDemoRoutesv1`,
`curl_testv1`, `ajax-examples.html`, `validation-examples.html`), so each step can be shown in the lecture.
Every week has its own `README.md` with the learning goals, the versions, what changed and a lecture demo script.

| Week | Topic | What is new in the code |
|------|-------|-------------------------|
| [01](week-01) | Frontend/backend project structure | static SPA (spapp, Bootstrap), backend skeleton, ERD, AGENTS.md, AI_LOG.md |
| [02](week-02) | Backend three-tier architecture | `database/campusevents.sql` (PK, FK, UNIQUE, CHECK), config template |
| [03](week-03) | DAO layer | DAO v1 → v4 (SQL injection → prepared statements → BaseDao), Phinx migrations & seeders |
| [04](week-04) | Business logic & services | services v1 → v3, Validator, exceptions with HTTP status, transactions |
| [05](week-05) | Presentation layer | REST routes v1 → v3, global error handler, `docs/api.http` |
| [06](week-06) | OpenAPI + external API | Swagger |
| [07](week-07) | Middleware & authentication, SSO | JWT, AuthMiddleware v1 → v2, logging, 2FA (TOTP), Google SSO |
| [08](week-08) | Authorization | RoleMiddleware v1 → v2, ownership rules, admin vs. student dashboard |
| [09](week-09) | API testing | curl v1 → PHPUnit (unit with mocks + API), Postman collection + newman |
| [10](week-10) | Ajax | frontend connected: XHR → fetch → $.ajax → RestClient, page scripts |
| [11](week-11) | Frontend MVC | services / controllers / views |
| [12](week-12) | Frontend validation & security | jQuery Validation v1 → v3, XSS escaping, security headers |
| [13](week-13) | CI/CD | GitHub Actions, Dockerfile, Docker Compose, CORS, deployment guide |
| [14](week-14) | Advanced web protocols | WebSockets (Ratchet) – live activity |

## Run any week
```bash
cd week-NN
mysql -u root -p < database/campusevents.sql                      # weeks 02+
cp backend/config/config.example.php backend/config/config.php    # set your DB password
cd backend && composer install && cd ..                           # weeks 01+
php -S localhost:8000 server.php                                  # open http://localhost:8000/frontend/
```
Test accounts (weeks 07+): `admin@campusevents.ba` / `Admin123!` · `ema@`, `tarik@`, `lana@campusevents.ba` / `Student123!`

`vendor/`, `composer.lock`, `config.php`, `.env` and logs are not part of the snapshots – they are created locally.
