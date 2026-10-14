# Week 03 – DAO Layer

> Builds on **week 02**. Only the **data access layer** is new – no services (week 04), no routes (week 05).
> **Milestone 1 is due this Monday (26.10.2026)**; Milestone 2 (database + DAO) next Monday.

## Learning goals
- Connect PHP to MySQL with **PDO**.
- Understand **SQL injection** and prevent it with **prepared statements**.
- Remove duplication step by step until every table needs only a tiny DAO class (`BaseDao`).
- Know why column names must be **whitelisted** and why counters are changed in one SQL statement.

## Versions (show them in this order)
| Step | Files | What it shows | Run |
|------|-------|---------------|-----|
| v1 | `rest/dao/EventDaov1.php` | hard-coded password, values glued into SQL → **SQL injection** | `php rest/dao/testv1.php` |
| v2 | `rest/dao/EventDaov2.php` | `config.php`, **prepared statements** (`?` and `:name`), full CRUD – but long and not reusable | `php rest/dao/testv2.php` |
| v3 | `rest/dao/BaseDaov1.php`, `EventDaov3.php`, `ClubDaov3.php` | first base class: connection + `get_all` / `get_by_id` inherited; INSERT/UPDATE/DELETE still by hand | `php rest/dao/testv3.php` |
| v4 (final) | `rest/dao/BaseDao.php` + `UserDao`, `ClubDao`, `CategoryDao`, `EventDao`, `RegistrationDao`, `ReviewDao` | generic CRUD built from arrays, column whitelist, one shared connection, transactions, JOINs, atomic seat counter | `php rest/dao/testv4.php` |

All commands are run from the `backend` folder (run `composer install` once). **v4 is the version used from week 04 on.**
(`EventDaov1` has the DB password hard-coded – edit it if your local root password is not empty.)

## How to run
```bash
mysql -u root -p < database/campusevents.sql
cp backend/config/config.example.php backend/config/config.php     # set your password
cd backend
php rest/dao/testv1.php     # ... testv2, testv3, testv4
```
Expected highlights:
```
testv1:  Search "' OR 1=1 -- " -> 12 event(s)   <- ALL events!
testv2:  Search "' OR 1=1 -- " -> 0 event(s)    <- safe
testv4:  Added: [name] => Test category ...      ("hacker" key silently dropped)
         Take a seat at the sold-out Robot Build Day -> rows changed: 0 (refused)
```

## Migrations & seeders (Phinx)
`database/campusevents.sql` creates everything at once. In real teams the schema **evolves**, so every change is a
small, versioned PHP file – a *migration* – committed to Git. Every developer (and the server) runs
`vendor/bin/phinx migrate` and gets exactly the same schema. *Seeders* fill the tables with sample or fake data.

| Path | What it shows |
|------|---------------|
| `backend/phinx.php` | Phinx configuration – uses the same `Config` class as the DAOs |
| `backend/db/migrations/20261021000001_create_users_table.php` … `000004` | one migration per table, written with the table builder (`addColumn`, `addIndex`, `addForeignKey`) |
| `backend/db/migrations/20261021000005_add_check_constraints.php` | raw SQL in `up()` / `down()` when the builder has no method |
| `backend/db/seeds/CampusEventsSeeder.php` | the sample data (same rows as `campusevents.sql`) |
| `backend/db/seeds/FakeStudentsSeeder.php` | 20 random students generated with **Faker** |

```bash
# empty database "campusevents" must exist (CREATE DATABASE campusevents;)
cd backend && composer install
vendor/bin/phinx migrate          # creates all tables, remembers what ran in table "phinxlog"
vendor/bin/phinx seed:run         # inserts sample data + fake students
vendor/bin/phinx rollback         # undo the last migration
vendor/bin/phinx status           # which migrations ran?
vendor/bin/phinx create AddPhoneToUsers   # new empty migration
```
> Use **either** `campusevents.sql` **or** migrations for one database, not both. Seeders are meant for an empty database.
> Phinx columns are nullable unless you write `'null' => false` – a typical first-migration bug.
