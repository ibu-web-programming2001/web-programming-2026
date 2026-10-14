# Week 02 – Backend Three-Tier Architecture

> Builds on **week 01**. Frontend unchanged (static).
> This week is about **structure and the database** – there is no DAO/service/route code yet.
> DAO layer: week 03 · services: week 04 · routes: week 05.

## Learning goals
- Understand the **three tiers** of a backend and *why* we separate them.
- Know which folder each kind of code goes into – before writing any of it.
- Turn the ERD from week 01 into a **MySQL database** (primary keys, foreign keys, constraints, sample data).
- Keep secrets out of Git with `config.php` / `config.example.php`.

## The three tiers
```
 Browser / Postman
        │  GET /events/1
        ▼
 ┌────────────────────────────┐  backend/rest/routes/     PRESENTATION LAYER   (week 05)
 │ read the HTTP request,      │  knows HTTP + JSON, no SQL, no rules
 │ send JSON + status code     │
 └─────────────┬──────────────┘
               ▼
 ┌────────────────────────────┐  backend/rest/services/   BUSINESS LOGIC LAYER (week 04)
 │ validation, rules           │  "max. 3 upcoming registrations", "no free seat → no registration"
 │ ("what is allowed?")        │  no HTTP, no SQL
 └─────────────┬──────────────┘
               ▼
 ┌────────────────────────────┐  backend/rest/dao/        DATA ACCESS LAYER    (week 03)
 │ SQL with PDO                │  no HTTP, no rules
 └─────────────┬──────────────┘
               ▼
            MySQL               database/campusevents.sql
```
Each layer only calls the layer **directly below** it. Benefits: you can change the database code without
touching routes, test rules without HTTP, and every file has one job (which makes the defense easier).

## What's new since week 01
| Path | Change |
|------|--------|
| `database/campusevents.sql` | **New** – 6 tables (PK, FK, UNIQUE, CHECK, ENUM) + sample data (6 clubs, 5 categories, 12 events, 11 registrations, 3 reviews) + 4 test accounts |
| `backend/rest/dao/`, `backend/rest/services/`, `backend/rest/routes/` | Still empty – each tier gets its code in the coming weeks |

`backend/config/config.example.php` (from week 01) is now important: copy it to `config.php` and set your DB password.

## Test accounts
| Email | Password | Role | Note |
|-------|----------|------|------|
| admin@campusevents.ba | Admin123! | admin | |
| ema@campusevents.ba | Student123! | student | already has 3 upcoming registrations (the limit) |
| tarik@campusevents.ba | Student123! | student | |
| lana@campusevents.ba | Student123! | student | has one cancelled registration |

## How to run
```bash
mysql -u root -p < database/campusevents.sql     # or phpMyAdmin → Import
cp backend/config/config.example.php backend/config/config.php
```

## Lecture demo script
1. Draw the three tiers on the board; map each one to a folder in `backend/`.
2. Go through `campusevents.sql` next to `docs/ERD.md`: every 1:N line in the ERD becomes a `FOREIGN KEY`.
3. Import it and show the tables in phpMyAdmin / MySQL Workbench (Designer view shows the relations).
4. Show the constraints working:
   ```sql
   DELETE FROM clubs WHERE id = 1;                                    -- fails: club has events (ON DELETE RESTRICT)
   INSERT INTO reviews (user_id, event_id, rating) VALUES (2, 9, 3);  -- fails: one review per student and event
   UPDATE events SET available_seats = 300 WHERE id = 1;              -- fails: CHECK available_seats ≤ capacity
   ```
5. Why are the event dates `CURDATE() + INTERVAL ...`? (sample data always has upcoming *and* past events)
6. Passwords in `users` are **bcrypt hashes** – never plain text (login comes in week 07).
7. Show `config.example.php` vs. `config.php` and the `.gitignore` entry – *never commit passwords*.
8. Ask: *"Where would the rule 'a student can have at most 3 upcoming registrations' go?"* → services (week 04).
