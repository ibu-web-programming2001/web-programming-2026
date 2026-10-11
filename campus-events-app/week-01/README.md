# Week 01 – Frontend/Backend Project Structure

> **Milestone 1** (due Sun 25.10.2026): project structure, static SPA frontend, draft ERD, README draft.

## Learning goals
- Understand how a full-stack project is split into a **frontend** (HTML/CSS/JS) and a **backend** (PHP REST API).
- Build a **Single Page Application** with the spapp router: one `index.html`, many views, no page reloads.
- Use Bootstrap 5 for a consistent, **responsive** theme (sidebar → offcanvas menu on mobile).
- Plan the database with an **ERD** before writing any SQL.

## What's in this week
| Path | What it shows |
|------|---------------|
| `frontend/index.html` | App shell: sidebar, top bar and one `<section>` per view |
| `frontend/js/app.js` | spapp setup, active menu link, auth layout for login/register |
| `frontend/views/*.html` | All pages with **static** data: dashboard, events, event details, clubs, categories, registrations, weather planner, users, profile, login, register |
| `frontend/css/style.css` | Theme on top of Bootstrap (colors, sidebar, cards) |
| `backend/index.php` | Plain PHP "hello world" (JSON) – proves the backend runs (FlightPHP comes in week 05) |
| `backend/config/config.example.php` | Configuration template (the real `config.php` is never committed) |
| `backend/{rest/{routes,services,dao},middleware,logs}` | Empty folders – filled in the coming weeks |
| `docs/ERD.md` | Entity Relationship Diagram (Mermaid – GitHub renders it) |
| `AGENTS.md`, `docs/AI_LOG.md` | Required AI documentation |
| `.gitignore` | Keeps `config.php`, `vendor/` and logs out of Git |
| `server.php` | Router for PHP's built-in server (not needed with XAMPP/Apache) |

## How to run
```bash
cd backend && composer install && cd ..
php -S localhost:8000 server.php
```
- Frontend: <http://localhost:8000/frontend/>
- Backend: <http://localhost:8000/backend/>

*XAMPP/MAMP:* copy the week folder into `htdocs` and open `http://localhost/<folder>/frontend/`.

## Lecture demo script
1. Open `index.html` – show that it contains **no page content**, only empty `<section>`s.
2. Click through the menu and watch the URL hash change (`#events`, `#clubs`) – no reload in the Network tab.
3. Open `views/events.html` – a view is just an HTML fragment loaded into its section.
4. Resize the browser below 992 px – the sidebar becomes an offcanvas menu (mobile friendly).
5. Explain why `dashboard` is the **last** section (default view in `spapp.css`).
6. Show the backend: `backend/index.php`, `/backend/`, and `.htaccess` rewriting.
7. Walk through `docs/ERD.md`: 6 entities, 1:N relations, registrations/reviews as the students ↔ events link.

## Exercise
Add a new static view `#about` ("About CampusEvents") (menu link + section + `views/about.html`).
