-- =====================================================================
-- Lab 03 – Task 6 (SOLUTION): club announcements
-- Run AFTER campusevents.sql:   mysql -u root -p campusevents < database/announcements.sql
-- =====================================================================
USE campusevents;

DROP TABLE IF EXISTS announcements;

CREATE TABLE announcements (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    club_id     INT          NOT NULL,
    title       VARCHAR(150) NOT NULL,
    body        TEXT         NOT NULL,
    is_pinned   BOOLEAN      NOT NULL DEFAULT FALSE,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_announcements_club FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE
);

INSERT INTO announcements (club_id, title, body, is_pinned, created_at) VALUES
(1, 'Band auditions this Friday',   'Bring your instrument to the Amphitheatre at 17:00. Singers welcome too.', TRUE,  NOW() - INTERVAL 2 DAY),
(2, 'Hackathon teams are open',     'Find a team of 3–4 in the GDSC channel before registration closes.',       FALSE, NOW() - INTERVAL 1 DAY),
(6, 'New robot kits have arrived',  'Twenty new kits are in the Robotics Lab – ask a mentor before using one.',  FALSE, NOW() - INTERVAL 3 DAY);
