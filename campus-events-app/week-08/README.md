# Week 08 – Authorization

> Builds on **week 07**. Frontend unchanged. Part of **Milestone 4**

## Learning goals
- Tell **authentication** (401 – *who are you?*) from **authorization** (403 – *are you allowed?*).
- Implement **role-based access control** as *reusable* middleware.
- Implement **ownership rules** ("students can only cancel their own registrations") in services.
- Never trust identity data from the request body – take it from the token.

## Permission matrix
| Resource | student | admin |
|----------|---------|-------|
| events, clubs, categories | read (+ event weather) | full CRUD |
| registrations | own registrations: read, **register self**, cancel | everything (register anybody, check in = *attended*, delete) |
| reviews | read all, write as self, edit/delete **own** | everything |
| users | – (only `/auth/me`) | full CRUD (cannot delete own account) |
| dashboard | personal numbers | campus statistics |
| weather forecast | ✓ | ✓ |

## Versions of the role check
| Step | File | Call | What it shows |
|------|------|------|---------------|
| v1 | `rest/routes/RoleDemoRoutesv1.php` | `DELETE /v1/categories/{id}` | `if ($user['role'] !== 'admin')` copied into the route |
| v2 (final) | `middleware/RoleMiddleware.php` | `DELETE /categories/{id}` | `->addMiddleware(new RoleMiddleware([Roles::ADMIN]))` – one line, reusable for any roles |

## What's new since week 07
| Path | Change |
|------|--------|
| `backend/middleware/RoleMiddleware.php` | **New** – `new RoleMiddleware([Roles::ADMIN])`, wrong role → 403 |
| `backend/rest/routes/RoleDemoRoutesv1.php` | **New** – role checked by hand (version 1) |
| `backend/index.php` | Loads `RoleMiddleware`; the week-05 demo routes (`/add_event`, `/v2/events`) are now admin-only – they change data |
| `backend/rest/routes/{Event,Club,Category}Routes.php` | POST/PUT/DELETE: `->addMiddleware(new RoleMiddleware([Roles::ADMIN]))` |
| `backend/rest/routes/UserRoutes.php` | Every route admin-only; delete passes the current user |
| `backend/rest/routes/RegistrationRoutes.php` | Uses `Flight::get('user')`; status change, check-in and delete admin-only |
| `backend/rest/routes/ReviewRoutes.php` | Author always = logged-in user |
| `backend/rest/services/BaseService.php` | + `check_owner($entity, $current_user)` → 403 |
| `backend/rest/services/RegistrationService.php` | + `get_for_user`, `get_by_id_for`; students register only themselves; `cancel` checks the owner |
| `backend/rest/services/ReviewService.php` | `add/update/delete` take the current user |
| `backend/rest/services/UserService.php` | Cannot delete your own account |
| `backend/rest/services/DashboardService.php` | + `get_for_user` – admin vs. student statistics |

## How to run
Same as week 07. Use `docs/api.http` – the *Authorization* section logs in as **Lana (student)**.
