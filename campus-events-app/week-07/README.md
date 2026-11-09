# Week 07 – Middleware and Authentication, SSO

> Builds on **week 06**. Frontend unchanged.

## Learning goals
- **Authentication** ("who are you?") with **JWT**: register, log in, send the token, verify it.
- Store passwords as **bcrypt hashes** (`password_hash` / `password_verify`).
- **Middleware** = reusable code that runs *around* routes, registered in FlightPHP – never copied into routes.
- **Logging**: an info log for every request and an error log for problems.
- Understand **SSO** (Single Sign-On) and how it fits with our own JWT.

## Request flow
```
 request ──► RequestLogMiddleware (start timer)
         ──► AuthMiddleware.before()      no/invalid/expired token → 401
         ──► route → service → dao
         ──► response
 exception anywhere ──► ErrorMiddleware.handle() → JSON + error.log
 end of request ──► info.log: "GET /events 200 user=1 role=admin 12ms"
```

## Versions (show them in this order)
| Step | Files | What it shows | Run |
|------|-------|---------------|-----|
| v1 | `rest/routes/AuthDemoRoutesv1.php` | token read and verified **by hand inside the route** – ~15 lines to copy into every protected route | `GET /v1/my-registrations` + `Authorization: Bearer <token>` |
| v2 (final) | `middleware/AuthMiddleware.php` + `Flight::group('', ..., [new AuthMiddleware()])` in `index.php` | written once, registered once – a new route in the group is protected automatically | `GET /events` without a token → 401 |

The demo routes of weeks 05 (`/get_events`, `/v2/events`) and 06 (`/weather/v1/forecast`) are still there, now inside the protected group.

## What's new since week 06
| Path | Change |
|------|--------|
| `backend/composer.json` | + `firebase/php-jwt` **^7** (6.x is blocked by Composer because of a security advisory) |
| `backend/config/config.example.php` | + `JWT_SECRET`, `JWT_EXPIRATION`, `GOOGLE_CLIENT_ID` |
| `backend/rest/routes/AuthDemoRoutesv1.php` | **New** – token check by hand (version 1) |
| `backend/rest/services/AuthService.php` | **New** – `register` (always role *student*), `login`, `get_profile`, `update_profile`, `create_token` |
| `backend/rest/routes/AuthRoutes.php` | **New** – `POST /auth/register`, `POST /auth/login` (public), `GET/PUT /auth/me` (route middleware) |
| `backend/middleware/AuthMiddleware.php` | **New** – reads `Authorization: Bearer <token>`, verifies it, `Flight::set('user', ...)`, else 401 |
| `backend/middleware/RequestLogMiddleware.php` | **New** – every request → `logs/info.log` (method, route, status, user, role, ms) |
| `backend/middleware/ErrorMiddleware.php` | **New** – error handler moved out of `index.php`; logs 401/403/500 to `logs/error.log` |
| `backend/utils/Logger.php` | **New** – `Logger::info()`, `Logger::error()` |
| `backend/rest/services/OpenMeteoService.php` | Logs every external call (info) and failure (error) |
| `backend/index.php` | Public routes vs. `Flight::group('', ..., [new AuthMiddleware()])` |
| `backend/.htaccess` | Passes the `Authorization` header to PHP on Apache |
| `backend/public/v1/docs/doc_setup.php` | `bearerAuth` security scheme → **Authorize** button in Swagger |
| `backend/utils/Totp.php`, 2FA/SSO routes, migration | **Optional** 2FA (TOTP) and Google SSO – see below |
| `database/campusevents.sql` | + `two_factor_secret`, `two_factor_enabled` columns on `users` |
| `docs/api.http` | + authentication requests (login fills in `{{token}}` automatically) |

> ⚠️ **Update your `config.php`** – copy the new `JWT_SECRET`, `JWT_EXPIRATION` and `GOOGLE_CLIENT_ID` methods from `config.example.php`.
> Set `JWT_SECRET` to your own long random string (at least 32 characters).
>
> ⚠️ **Re-import `database/campusevents.sql`** – the `users` table has two new columns.

## API overview (new endpoints)
| Method | URL | Token | Status | Description |
|--------|-----|-------|--------|-------------|
| POST | `/auth/register` | – | 201 / 400 | new account, always role *student* |
| POST | `/auth/login` | – | 200 / 401 | returns `{"user": ..., "token": "..."}` |
| GET | `/auth/me` | ✔ | 200 / 401 | the logged-in user |
| PUT | `/auth/me` | ✔ | 200 / 400 / 401 | change your name / password |
| GET | `/v1/my-registrations` | ✔ | 200 / 401 | v1 demo – token checked inside the route |
| * | every route from weeks 05–06 | ✔ | … / 401 | now protected by `AuthMiddleware` |

Test accounts: `admin@campusevents.ba` / `Admin123!`, `ema@campusevents.ba` / `Student123!`.

## How to run
Start MySQL, then:
1. Import `database/campusevents.sql` in phpMyAdmin.
2. Copy `backend/config/config.example.php` to `backend/config/config.php` and set your database credentials and `JWT_SECRET`.
3. Install the dependencies from the `backend` folder (PHP 8.2 or newer is required):
   ```bash
   cd backend
   composer install
   ```

The backend runs on **your own computer**, so every URL starts with `http://localhost`. Below, `{base}` means your base URL:

| Setup | `{base}` |
|-------|----------|
| XAMPP/WAMP (Apache) | `http://localhost/campus-events-app/week-07/backend` |
| PHP built-in server | `http://localhost:8000/backend` |

If Apache does not use port 80 (e.g. XAMPP moved to `8080`, MAMP uses `8888`), add the port: `http://localhost:8080/...`.

### With XAMPP/WAMP (Apache)
Copy `campus-events-app` into `htdocs` (XAMPP) or `www` (WAMP) and start Apache. The base URL is:
```
http://localhost/<your-project-folder>/backend
```
For example, with `campus-events-app` copied into `htdocs` or `www`:
```
http://localhost/campus-events-app/week-07/backend
```
If every URL except `{base}/` gives an Apache 404, `mod_rewrite` is off. On WAMP, turn it on from the tray icon: *Apache → Apache modules → rewrite_module*.

If every protected route answers `401 Missing authentication token.` even though you send the header, Apache is dropping
`Authorization` – make sure `backend/.htaccess` was copied too.

### With the PHP built-in server
Run from the `week-07` folder:
```bash
php -S localhost:8000 server.php
```
The base URL is:
```
http://localhost:8000/backend
```

### Watch the logs
In a second terminal, from the `week-07` folder:
```bash
tail -f backend/logs/info.log backend/logs/error.log
```
On Windows, open the two files in VS Code instead – they update while you send requests.

### In the browser
Add these paths to your base URL (`{base}`):
```
{base}/                      -> public, "The backend is running."
{base}/public/v1/docs/       -> Swagger UI (log in, then "Authorize")
{base}/events                -> 401, the browser cannot send a token
```

### In Swagger UI
1. `POST /auth/login` → *Try it out* → `{"email":"admin@campusevents.ba","password":"Admin123!"}` → copy the `token`.
2. Click **Authorize** (top right), paste the token (without `Bearer`) → all locks close.
3. Call `GET /auth/me` or `GET /events` – the token is now sent with every request.

### In Postman
Log in first, then add the token to every protected request: *Authorization → Bearer Token → paste the token*.
For `POST` and `PUT` set *Body → raw → JSON*.

| Method | URL | Token | Body | Expected |
|--------|-----|-------|------|----------|
| GET | `{base}/events` | – | – | 401 |
| POST | `{base}/auth/login` | – | `{"email":"admin@campusevents.ba","password":"Admin123!"}` | 200 + token |
| POST | `{base}/auth/login` | – | `{"email":"admin@campusevents.ba","password":"wrong"}` | 401 (+ `LOGIN FAILED` in `error.log`) |
| GET | `{base}/events` | ✔ | – | 200, list |
| GET | `{base}/auth/me` | ✔ | – | 200, the admin |
| GET | `{base}/v1/my-registrations` | ✔ | – | v1 demo: 200 |
| GET | `{base}/events` | `abc.def.ghi` | – | 401 |
| POST | `{base}/auth/register` | – | `{"first_name":"Amar","last_name":"Hodžić","email":"amar@campusevents.ba","faculty":"FENS","password":"Secret123","password_confirm":"Secret123","role":"admin"}` | 201, role is still *student* |
| PUT | `{base}/auth/me` | ✔ | `{"first_name":"Admin","password":"New123!","password_confirm":"Other"}` | 400 |

All of these requests are also in `docs/api.http` (VS Code *REST Client* or PhpStorm); change `@base` when using XAMPP/WAMP.

Expected output (shortened):
```
GET  {base}/events             401  {"error":"Missing authentication token."}
POST {base}/auth/login         200  {"user":{"id":1,"email":"admin@campusevents.ba","role":"admin",...},"token":"eyJ0eXAiOiJKV1Qi..."}
POST {base}/auth/login         401  {"error":"Invalid email or password."}
GET  {base}/events  (bad token) 401 {"error":"Invalid authentication token."}
```
`logs/info.log` and `logs/error.log`:
```
[...] INFO GET /events 200 user=1 role=admin 14ms
[...] INFO LOGIN user=1 role=admin
[...] ERROR LOGIN FAILED email=admin@campusevents.ba
```

### Things to try
- Paste the token into <https://jwt.io>: the payload is **readable** – never put secrets in it.
  Change one character of the payload and call the API → 401 (signature does not match).
- Log in with an email that does not exist → the same `Invalid email or password.` (why?).
- Break something (e.g. a wrong column in a DAO) → the client gets a generic 500, `error.log` gets
  `PDOException: ... (EventDao.php:31)`.

## Optional: two-factor authentication (2FA) and Single Sign-On (SSO)
| Path | What it shows |
|------|---------------|
| `backend/utils/Totp.php` | TOTP (RFC 6238) in ~60 lines – the 6-digit codes of Google/Microsoft Authenticator |
| `backend/rest/services/AuthService.php` | `setup_two_factor`, `enable_two_factor`, `disable_two_factor`; login asks for `code` when 2FA is on; `login_with_google` |
| `backend/rest/routes/AuthRoutes.php` | `POST /auth/2fa/setup`, `/auth/2fa/enable`, `/auth/2fa/disable`, `POST /auth/google` |
| `backend/db/migrations/20261118000001_add_two_factor_to_users.php` | the schema evolves: two new columns via a **migration** (also added to `campusevents.sql`) |
| `backend/config/config.example.php` | + `GOOGLE_CLIENT_ID` (empty = SSO disabled) |

**2FA flow:** setup → scan the `otpauth://` link as a QR code (or type the secret into the app) → enable with the
first code → from now on `POST /auth/login` without `code` answers `{"two_factor_required": true}` and **no token**;
with the right `code` you get the token. Try it with `docs/api.http`.

**SSO with Google:**
1. The frontend shows *"Sign in with Google"* (Google Identity Services) and receives an **ID token** – a JWT signed by Google.
2. It sends it to `POST /auth/google`.
3. The backend asks Google to verify it (`tokeninfo`), checks `aud` = **our** client id and `email_verified`,
   finds or creates the user (role *student*) by email.
4. The backend returns **our own JWT** – from here on everything works exactly as with a normal login.

So `AuthMiddleware` does not change at all – only the way the user proves who they are.
To try SSO you need a client id from Google Cloud Console (APIs & Services → Credentials → OAuth client ID, type *Web*).

## Notes
- Every logged-in user can still do everything (a student can delete events!). **Authorization** (roles) comes in week 08.
- Tokens expire after `JWT_EXPIRATION` seconds (default 24 h) → `401 Your session has expired. Please log in again.`
