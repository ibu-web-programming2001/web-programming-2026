# AI usage log

> Required by the project rules: one section per milestone with the AI tools used, the most
> important prompts, and **at least two cases where the AI was wrong** – what was wrong,
> how it was noticed, and how it was fixed. If you do not use AI, write that here.

*The entries below are worked examples for the lecture – they show the expected format and
level of detail, using real pitfalls of this stack.*

## Milestone 1 – Setup, static frontend, ERD

**Tools used:** Claude Code (static HTML views, CSS layout), reviewed and adjusted by hand.

**Most important prompts**
- "Create a Bootstrap 5 sidebar layout that turns into an offcanvas menu on mobile. Do not use any JS framework."
- "Generate a Mermaid ER diagram for users, clubs, categories, events, registrations and reviews."

**Cases where the AI was wrong**
1. *What:* the sidebar background disappeared on desktop.
   *How noticed:* the menu text was unreadable at ≥ 992 px width.
   *Fix:* Bootstrap's `.offcanvas-lg` forces `background-color: transparent` on large screens,
   so the colour was moved to the wrapping `<aside class="sidebar">`.
2. *What:* the AI put the default view (`dashboard`) as the first `<section>`.
   *How noticed:* the dashboard was always visible under every other page.
   *Fix:* spapp shows the **last** section by default (`css/spapp.css`), so `dashboard` was moved to the end.

## Milestone 2 – Database and DAO layer

**Tools used:** Claude Code for the first version of `campusevents.sql` and `BaseDao`; all queries were run and checked manually.

**Most important prompts**
- "Write a MySQL schema for the ERD in docs/ERD.md with foreign keys and CHECK constraints."
- "Write a PHP BaseDao with PDO prepared statements: get_all, get_by_id, add, update, delete."

**Cases where the AI was wrong**
1. *What:* the generated `add()` built the column list directly from the request keys.
   *How noticed:* sending `{"name":"x","id) VALUES (1); DROP TABLE events; --":1}` produced an SQL error –
   column names cannot be protected with placeholders.
   *Fix:* every DAO now declares its writable columns and `only_columns()` drops all other keys.
2. *What:* the AI reused the placeholder `:delta` twice in one query.
   *How noticed:* works with PDO's emulated prepares, but fails with `SQLSTATE[HY093]` when
   `PDO::ATTR_EMULATE_PREPARES` is turned off.
   *Fix:* use two different placeholders (`:delta`, `:delta2`).
