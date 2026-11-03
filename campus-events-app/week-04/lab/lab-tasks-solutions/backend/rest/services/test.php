<?php
/*
 * Setup check (Task 1) – run from the backend folder:   php rest/services/test.php
 * Reads every table through its service. It must print 5 categories, 6 clubs, 12 events, 11 registrations, 3 reviews and 4 users.
 */
require_once __DIR__ . '/CategoryService.php';
require_once __DIR__ . '/ClubService.php';
require_once __DIR__ . '/EventService.php';
require_once __DIR__ . '/RegistrationService.php';
require_once __DIR__ . '/ReviewService.php';
require_once __DIR__ . '/UserService.php';

echo "Categories:    " . count((new CategoryService())->get_all()) . "\n";
echo "Clubs:         " . count((new ClubService())->get_all()) . "\n";
echo "Events:        " . count((new EventService())->get_all()) . "\n";
echo "Registrations: " . count((new RegistrationService())->get_all()) . "\n";
echo "Reviews:       " . count((new ReviewService())->get_all()) . "\n";
echo "Users:         " . count((new UserService())->get_all()) . "\n";

echo "\nA rule from ClubService (finished example):\n";
try {
    (new ClubService())->add(['name' => 'Robotics Club']);
} catch (HttpException $e) {
    echo get_class($e) . " [{$e->get_status()}]: {$e->getMessage()}\n";
}
