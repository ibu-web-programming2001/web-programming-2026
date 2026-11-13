# CampusEvents – Entity Relationship Diagram

CampusEvents is a single-page application for student events. Students discover events
(concerts, hackathons, sports, workshops, trips), register and review them; administrators
manage events, clubs, categories, registrations and users.

```mermaid
erDiagram
    USERS ||--o{ REGISTRATIONS : "registers"
    USERS ||--o{ REVIEWS : "writes"
    EVENTS ||--o{ REGISTRATIONS : "has"
    EVENTS ||--o{ REVIEWS : "receives"
    CLUBS ||--o{ EVENTS : "organises"
    CATEGORIES ||--o{ EVENTS : "groups"

    USERS {
        int id PK
        varchar first_name
        varchar last_name
        varchar email UK
        varchar password "bcrypt hash"
        enum role "admin | student"
        varchar faculty "optional"
        timestamp created_at
    }
    CLUBS {
        int id PK
        varchar name UK
        varchar faculty
        smallint founded_year
        text description
    }
    CATEGORIES {
        int id PK
        varchar name UK
        varchar description
    }
    EVENTS {
        int id PK
        varchar title
        int club_id FK
        int category_id FK
        varchar location
        varchar city
        datetime starts_at
        int capacity
        int available_seats
        text description
    }
    REGISTRATIONS {
        int id PK
        int user_id FK
        int event_id FK
        datetime registered_at
        datetime cancelled_at "NULL unless cancelled"
        enum status "registered | cancelled | attended"
    }
    REVIEWS {
        int id PK
        int user_id FK
        int event_id FK
        tinyint rating "1 - 5"
        text comment
    }
```

## Relationships

| Relationship            | Type | Rule                                                                  |
|-------------------------|------|-----------------------------------------------------------------------|
| Club → Events           | 1:N  | A club organises many events; an event has exactly one organiser.     |
| Category → Events       | 1:N  | A category groups many events; an event belongs to one category.      |
| User → Registrations    | 1:N  | A student can register for many events (max. 3 upcoming at a time).   |
| Event → Registrations   | 1:N  | An event has many registrations (limited by `available_seats`).       |
| User → Reviews          | 1:N  | A student writes many reviews, but only **one review per event**.     |
| Event → Reviews         | 1:N  | An event can have many reviews.                                       |

`USERS` + `EVENTS` + `REGISTRATIONS` / `REVIEWS` form two many-to-many relationships
(students ↔ events) resolved through the `registrations` and `reviews` tables.
