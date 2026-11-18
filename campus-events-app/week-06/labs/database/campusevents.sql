-- =====================================================================
-- CampusEvents – student events database (MySQL 8)
-- Import:  mysql -u root -p < database/campusevents.sql
--          (or phpMyAdmin → Import → choose this file)
--
-- Test accounts (password in brackets):
--   admin@campusevents.ba  (Admin123!)    – role admin
--   ema@campusevents.ba    (Student123!)  – role student (already has 3 upcoming registrations = the limit)
--   tarik@campusevents.ba  (Student123!)  – role student
--   lana@campusevents.ba   (Student123!)  – role student
-- =====================================================================

-- Read this file as UTF-8 (names like Hadžić, Begić, Trebević)
SET NAMES utf8mb4;

DROP DATABASE IF EXISTS campusevents;
CREATE DATABASE campusevents CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campusevents;

-- ---------------------------------------------------------------------
-- Tables
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    first_name  VARCHAR(50)  NOT NULL,
    last_name   VARCHAR(50)  NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,                 -- bcrypt hash, never plain text
    role        ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    faculty     VARCHAR(10)  NULL,                     -- FENS, FASS, FBA, FEDU
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE clubs (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL UNIQUE,
    faculty      VARCHAR(40)  NULL,
    founded_year SMALLINT     NULL,
    description  TEXT         NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(60)  NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE events (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(150) NOT NULL,
    club_id         INT          NOT NULL,
    category_id     INT          NOT NULL,
    location        VARCHAR(120) NOT NULL,
    city            VARCHAR(60)  NOT NULL DEFAULT 'Sarajevo',
    starts_at       DATETIME     NOT NULL,
    capacity        INT          NOT NULL,
    available_seats INT          NOT NULL,
    description     TEXT         NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_events_club     FOREIGN KEY (club_id)     REFERENCES clubs(id)      ON DELETE RESTRICT,
    CONSTRAINT fk_events_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    CONSTRAINT chk_events_seats   CHECK (available_seats BETWEEN 0 AND capacity),
    CONSTRAINT chk_events_capacity CHECK (capacity > 0)
);

CREATE TABLE registrations (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT      NOT NULL,
    event_id      INT      NOT NULL,
    registered_at DATETIME NOT NULL,
    cancelled_at  DATETIME NULL,                      -- NULL unless the registration was cancelled
    status        ENUM('registered', 'cancelled', 'attended') NOT NULL DEFAULT 'registered',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_registrations_user  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    CONSTRAINT fk_registrations_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

CREATE TABLE reviews (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT     NOT NULL,
    event_id    INT     NOT NULL,
    rating      TINYINT NOT NULL,
    comment     TEXT    NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_user  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    CONSTRAINT fk_reviews_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT uq_reviews_user_event UNIQUE (user_id, event_id),   -- one review per event per student
    CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)
);

-- ---------------------------------------------------------------------
-- Sample data
-- ---------------------------------------------------------------------
INSERT INTO users (id, first_name, last_name, email, password, role, faculty, created_at) VALUES
(1, 'Lejla', 'Hadžić', 'admin@campusevents.ba', '$2y$10$0Hge22rw38Y05WNSVUtghO.6HmmiOeHLTQFdBesqg33d5z3feOvBG', 'admin',   NULL,   '2026-09-01 09:00:00'),
(2, 'Ema',   'Kovač',  'ema@campusevents.ba',   '$2y$10$TxLK/Oju/TDbgoGjSYisyeJl1vBJlpwilOPqh5Sm.XMuec4FZp6ni', 'student', 'FENS', '2026-09-03 10:15:00'),
(3, 'Tarik', 'Begić',  'tarik@campusevents.ba', '$2y$10$TxLK/Oju/TDbgoGjSYisyeJl1vBJlpwilOPqh5Sm.XMuec4FZp6ni', 'student', 'FBA',  '2026-09-05 12:30:00'),
(4, 'Lana',  'Mehić',  'lana@campusevents.ba',  '$2y$10$TxLK/Oju/TDbgoGjSYisyeJl1vBJlpwilOPqh5Sm.XMuec4FZp6ni', 'student', 'FASS', '2026-09-10 08:45:00');

INSERT INTO clubs (id, name, faculty, founded_year, description) VALUES
(1, 'IBU Music Club',                'FASS',          2015, 'Bands, open mics and concerts on campus.'),
(2, 'Google Developer Student Club', 'FENS',          2019, 'Workshops, hackathons and tech talks for future developers.'),
(3, 'IBU Sports Association',        'All faculties', 2012, 'Tournaments, hikes and recreation for every student.'),
(4, 'Debate & Culture Club',         'FASS',          2016, 'Debates, film nights, trips and culture.'),
(5, 'Entrepreneurship Club',         'FBA',           2018, 'Pitch nights, career fairs and networking.'),
(6, 'Robotics Club',                 'FENS',          2020, 'Build robots, compete and learn electronics.');

INSERT INTO categories (id, name, description) VALUES
(1, 'Music',   'Concerts, open mics and jam sessions'),
(2, 'Tech',    'Hackathons, workshops and tech talks'),
(3, 'Sports',  'Tournaments, hikes and fitness'),
(4, 'Culture', 'Film, theatre, debates and trips'),
(5, 'Career',  'Career fairs, pitches and networking');

-- Dates are relative to the import day, so "upcoming", "starting soon" and "past" always look realistic.
-- available_seats = capacity – active registrations of our 3 students
INSERT INTO events (id, title, club_id, category_id, location, city, starts_at, capacity, available_seats, description) VALUES
(1,  'Welcome Concert',                 1, 1, 'IBU Amphitheatre', 'Sarajevo', TIMESTAMP(CURDATE() + INTERVAL 3 DAY,  '19:00:00'), 200, 198, 'Start the semester with live music from student bands, food trucks and a surprise headliner.'),
(2,  'Hack the Campus – 24h Hackathon', 2, 2, 'FENS Lab 2',       'Sarajevo', TIMESTAMP(CURDATE() + INTERVAL 10 DAY, '09:00:00'), 60,  59,  '24 hours, teams of 3–4, mentors from local companies and prizes for the best apps.'),
(3,  'Futsal Tournament',               3, 3, 'IBU Sports Hall',  'Sarajevo', TIMESTAMP(CURDATE() + INTERVAL 6 DAY,  '17:00:00'), 40,  39,  'Faculty teams compete for the IBU futsal cup.'),
(4,  'Git & GitHub Workshop',           2, 2, 'Room B-103',       'Sarajevo', TIMESTAMP(CURDATE() + INTERVAL 2 DAY,  '16:00:00'), 30,  29,  'Branches, pull requests and conflicts – everything you need for your project milestones.'),
(5,  'Movie Night: Classic Cinema',     4, 4, 'Main Hall',        'Sarajevo', TIMESTAMP(CURDATE() + INTERVAL 8 DAY,  '20:00:00'), 120, 120, 'A classic film on the big screen, popcorn included.'),
(6,  'Startup Pitch Night',             5, 5, 'Business Lab',     'Sarajevo', TIMESTAMP(CURDATE() + INTERVAL 14 DAY, '18:00:00'), 80,  80,  'Student teams pitch their ideas to investors in five minutes.'),
(7,  'Robot Build Day',                 6, 2, 'Robotics Lab',     'Sarajevo', TIMESTAMP(CURDATE() + INTERVAL 5 DAY,  '10:00:00'), 2,   0,   'Build a line-following robot – very limited places.'),
(8,  'Hiking Trip to Trebević',         3, 3, 'Trebević',         'Sarajevo', TIMESTAMP(CURDATE() + INTERVAL 12 DAY, '08:00:00'), 25,  25,  'A day on the mountain above Sarajevo – bring good shoes.'),
(9,  'Open Mic Night',                  1, 1, 'Campus Café',      'Sarajevo', TIMESTAMP(CURDATE() - INTERVAL 7 DAY,  '20:00:00'), 50,  48,  'Sing, play or read poetry – everybody gets five minutes on stage.'),
(10, 'Career Fair 2026',                5, 5, 'IBU Campus',       'Sarajevo', TIMESTAMP(CURDATE() - INTERVAL 14 DAY, '10:00:00'), 300, 299, 'Meet more than 30 companies looking for interns and junior developers.'),
(11, 'Mostar Culture Trip',             4, 4, 'Old Bridge',       'Mostar',   TIMESTAMP(CURDATE() + INTERVAL 20 DAY, '08:00:00'), 45,  45,  'Guided tour of Mostar and the Old Bridge.'),
(12, 'Chess Blitz Night',               4, 4, 'Library Hall',     'Sarajevo', TIMESTAMP(CURDATE() + INTERVAL 1 DAY,  '18:00:00'), 16,  16,  'Five-minute games, Swiss system, beginners welcome.');

INSERT INTO registrations (user_id, event_id, registered_at, cancelled_at, status) VALUES
(2, 1,  NOW() - INTERVAL 5 DAY,  NULL, 'registered'),
(2, 2,  NOW() - INTERVAL 3 DAY,  NULL, 'registered'),
(2, 4,  NOW() - INTERVAL 1 DAY,  NULL, 'registered'),
(3, 1,  NOW() - INTERVAL 4 DAY,  NULL, 'registered'),
(3, 7,  NOW() - INTERVAL 2 DAY,  NULL, 'registered'),
(4, 7,  NOW() - INTERVAL 2 DAY,  NULL, 'registered'),
(4, 3,  NOW() - INTERVAL 6 DAY,  NULL, 'registered'),
(2, 9,  NOW() - INTERVAL 12 DAY, NULL, 'attended'),
(3, 10, NOW() - INTERVAL 20 DAY, NULL, 'attended'),
(4, 9,  NOW() - INTERVAL 12 DAY, NULL, 'attended'),
(4, 5,  NOW() - INTERVAL 8 DAY,  NOW() - INTERVAL 6 DAY, 'cancelled');

INSERT INTO reviews (user_id, event_id, rating, comment) VALUES
(2, 9,  5, 'Amazing atmosphere – I will definitely perform next time!'),
(4, 9,  4, 'Great performers, a bit crowded.'),
(3, 10, 5, 'Got my first internship interview here.');
