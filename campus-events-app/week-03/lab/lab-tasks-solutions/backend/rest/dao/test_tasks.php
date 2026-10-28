<?php
/*
 * Lab 03 – checker for Tasks 2–7.     Run from the backend folder:   php rest/dao/test_tasks.php
 *
 * Every section prints [TODO] until you write the method it needs.
 * Compare your output with the "Expected" lines in the Lab Tasks document.
 * The script cleans up after itself, so you can run it as often as you like.
 */
require_once __DIR__ . '/UserDao.php';
require_once __DIR__ . '/EventDao.php';
require_once __DIR__ . '/RegistrationDao.php';
require_once __DIR__ . '/ReviewDao.php';
require_once __DIR__ . '/StudentSearchDao.php';
if (file_exists(__DIR__ . '/AnnouncementDao.php')) {
    require_once __DIR__ . '/AnnouncementDao.php';
}

function title($text) { echo "\n=== $text ===\n"; }
function todo($what)  { echo "[TODO] $what is not implemented yet\n"; }
function titles($rows, $key = 'title') { return implode(', ', array_column($rows, $key)); }

/* ------------------------------------------------------------------ */
title('Task 2 – SQL injection in StudentSearchDao');
$search = new StudentSearchDao();

$row = $search->find_by_email("' OR '1'='1");
echo "find_by_email(\"' OR '1'='1\")       -> " . ($row === null ? "null  (SAFE)" : "{$row['email']}  (STILL VULNERABLE)") . "\n";

$rows = $search->search_by_name("' OR 1=1 -- ");
echo "search_by_name(\"' OR 1=1 -- \")     -> " . count($rows) . " row(s)  " . (count($rows) === 0 ? "(SAFE)" : "(STILL VULNERABLE)") . "\n";

$rows = $search->get_by_faculty("x' OR '1'='1");
echo "get_by_faculty(\"x' OR '1'='1\")     -> " . count($rows) . " row(s)  " . (count($rows) === 0 ? "(SAFE)" : "(STILL VULNERABLE)") . "\n";

$total = $search->count_registrations("2 OR 1=1");
echo "count_registrations(\"2 OR 1=1\")    -> $total  " . ($total === 4 ? "(SAFE – only user 2 was counted)" : "(STILL VULNERABLE)") . "\n";

echo "Normal use still works: " . $search->find_by_email('ema@campusevents.ba')['first_name']
   . ", search 'a' -> " . count($search->search_by_name('a'))
   . ", FENS students -> " . count($search->get_by_faculty('FENS')) . "\n";

/* ------------------------------------------------------------------ */
title('Task 3 – EventDao finders');
$events = new EventDao();

if (method_exists($events, 'get_by_club')) {
    echo "get_by_club(2):     " . titles($events->get_by_club(2)) . "\n";
} else todo('EventDao::get_by_club()');

if (method_exists($events, 'get_by_category')) {
    echo "get_by_category(4): " . titles($events->get_by_category(4)) . "\n";
} else todo('EventDao::get_by_category()');

if (method_exists($events, 'search')) {
    echo "search('night'):    " . titles($events->search('night')) . "\n";
    echo "search(\"' OR 1=1 -- \"): " . count($events->search("' OR 1=1 -- ")) . " row(s)\n";
} else todo('EventDao::search()');

/* ------------------------------------------------------------------ */
title('Task 4 – EventDao::filter()');
if (method_exists($events, 'filter')) {
    echo "no filters:                         " . count($events->filter()) . " events\n";
    echo "category Tech (2):                  " . titles($events->filter(['category_id' => 2])) . "\n";
    echo "city Mostar:                        " . titles($events->filter(['city' => 'Mostar'])) . "\n";
    echo "upcoming + at least 50 free seats:  " . titles($events->filter(['only_upcoming' => true, 'min_free_seats' => 50])) . "\n";
    echo "club 3 + category 3 + upcoming:     " . titles($events->filter(['club_id' => 3, 'category_id' => 3, 'only_upcoming' => true])) . "\n";
} else todo('EventDao::filter()');

/* ------------------------------------------------------------------ */
title('Task 5 – statistics');
$registrations = new RegistrationDao();
if (method_exists($registrations, 'count_per_event')) {
    foreach (array_slice($registrations->count_per_event(), 0, 4) as $r) {
        $ok = ((int) $r['taken'] + (int) $r['available_seats'] === (int) $r['capacity']) ? 'OK' : 'MISMATCH';
        printf("%-32s taken %2d  free %3d  capacity %3d  %s\n", $r['title'], $r['taken'], $r['available_seats'], $r['capacity'], $ok);
    }
} else todo('RegistrationDao::count_per_event()');

$reviews = new ReviewDao();
if (method_exists($reviews, 'average_per_club')) {
    foreach ($reviews->average_per_club() as $r) {
        printf("%-30s reviews %d  average %s\n", $r['club'], $r['review_count'], $r['average_rating'] ?? '-');
    }
} else todo('ReviewDao::average_per_club()');

$users = new UserDao();
if (method_exists($users, 'get_most_active')) {
    foreach ($users->get_most_active(2) as $u) {
        echo "{$u['first_name']} {$u['last_name']}: {$u['registrations']} registrations\n";
    }
} else todo('UserDao::get_most_active()');

/* ------------------------------------------------------------------ */
title('Task 6 – AnnouncementDao');
if (class_exists('AnnouncementDao')) {
    try {
        $announcements = new AnnouncementDao();
        echo "All (pinned first):  " . titles($announcements->get_all()) . "\n";
        echo "get_by_club(2):      " . titles($announcements->get_by_club(2)) . "\n";
        echo "get_latest(1):       " . titles($announcements->get_latest(1)) . "\n";
    } catch (PDOException $e) {
        echo "[ERROR] " . $e->getMessage() . "\n        Did you import database/announcements.sql?\n";
    }
} else todo('AnnouncementDao (rest/dao/AnnouncementDao.php)');

/* ------------------------------------------------------------------ */
title('Task 7 – transactions');
if (method_exists($registrations, 'register') && method_exists($registrations, 'cancel')) {
    $seats = function ($id) use ($events) { return $events->get_by_id($id)['available_seats']; };

    echo "Chess Blitz Night free seats: " . $seats(12) . "\n";
    $new = $registrations->register(4, 12);
    echo "register(4, 12)  -> registration #{$new['id']}, free seats now " . $seats(12) . "\n";

    try {
        $registrations->register(4, 12);
    } catch (Exception $e) {
        echo "register(4, 12) again -> refused: " . $e->getMessage() . "\n";
    }

    $registrations->cancel($new['id']);
    echo "cancel(#{$new['id']})      -> status " . $registrations->get_by_id($new['id'])['status'] . ", free seats back to " . $seats(12) . "\n";

    try {
        $registrations->register(2, 7);
    } catch (Exception $e) {
        echo "register(2, 7)   -> refused: " . $e->getMessage() . "\n";
    }

    try {
        $registrations->register(999, 12);   // user 999 does not exist -> the INSERT fails AFTER the seat was taken
    } catch (Exception $e) {
        echo "register(999, 12) -> failed, ROLLBACK – free seats still " . $seats(12) . "\n";
    }

    $registrations->delete($new['id']);   // clean up so the script can run again
} else todo('RegistrationDao::register() / cancel()');

/* ------------------------------------------------------------------ */
title('Task 8 – your own tests');
require __DIR__ . '/my_tests.php';
