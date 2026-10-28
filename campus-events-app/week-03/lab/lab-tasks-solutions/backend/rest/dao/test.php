<?php
/*
 * Setup check (Task 1) – php rest/dao/test.php
 * Every table has a DAO; all of them inherit generic CRUD from BaseDao.
 */
require_once __DIR__ . '/UserDao.php';
require_once __DIR__ . '/ClubDao.php';
require_once __DIR__ . '/CategoryDao.php';
require_once __DIR__ . '/EventDao.php';
require_once __DIR__ . '/RegistrationDao.php';
require_once __DIR__ . '/ReviewDao.php';

function title($text) { echo "\n=== $text ===\n"; }

title('READ – one DAO per table');
echo "Users:         " . count((new UserDao())->get_all()) . "\n";
echo "Clubs:         " . count((new ClubDao())->get_all()) . "\n";
echo "Categories:    " . count((new CategoryDao())->get_all()) . "\n";
echo "Events:        " . count((new EventDao())->get_all()) . "\n";
echo "Registrations: " . count((new RegistrationDao())->get_all()) . "\n";
echo "Reviews:       " . count((new ReviewDao())->get_all()) . "\n";

title('Event #9 – JOINs give club/category names and the rating in ONE query');
print_r((new EventDao())->get_by_id(9));

title('User by email – the password hash is only returned here (needed for login)');
print_r((new UserDao())->get_by_email('ema@campusevents.ba'));

title('Generic CRUD on categories');
$dao = new CategoryDao();

// "hacker" is NOT a column of categories -> silently dropped by only_columns()
$category = $dao->add(['name' => 'Test category', 'description' => 'Created by test.php', 'hacker' => 'DROP TABLE']);
echo "Added:   "; print_r($category);

$dao->update(['description' => 'Updated description'], $category['id']);
echo "Updated: "; print_r($dao->get_by_id($category['id']));

$dao->delete($category['id']);
echo "Deleted: "; var_dump($dao->get_by_id($category['id']));

title('Atomic seat counter (used by registrations in week 04)');
$events = new EventDao();
echo "Welcome Concert free seats: " . $events->get_by_id(1)['available_seats'] . "\n";
echo "Take one seat -> rows changed: " . $events->change_available_seats(1, -1) . "\n";
echo "Give it back  -> rows changed: " . $events->change_available_seats(1, +1) . "\n";
echo "Take a seat at the sold-out Robot Build Day -> rows changed: " . $events->change_available_seats(7, -1) . " (refused)\n";
echo "Add a 121st seat to Movie Night (capacity 120) -> rows changed: " . $events->change_available_seats(5, +1) . " (refused)\n";
