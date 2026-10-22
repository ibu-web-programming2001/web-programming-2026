<?php
/*
 * VERSION 1 demo – php rest/services/testv1.php
 * Without rules, nonsense is saved into the database.
 */
require_once __DIR__ . '/EventServicev1.php';

$service = new EventServicev1();

$event = $service->add([
    'title' => '',                    // empty title
    'club_id' => 1,
    'category_id' => 1,
    'location' => 'Room A-1',
    'starts_at' => '2020-01-01 10:00:00',   // in the past!
    'capacity' => 30,
    'available_seats' => 0            // sold out before anybody registered?! The client decides.
]);
echo "Saved an event with an EMPTY title, in 2020, sold out before anybody registered:\n";
print_r($service->get_by_id($event['id']));

$service->delete($event['id']);       // clean up

echo "\nAnd an event of an unknown club crashes with a database error:\n";
try {
    $service->add(['title' => 'Ghost Party', 'club_id' => 999, 'category_id' => 1, 'location' => 'Nowhere',
                   'starts_at' => '2027-01-01 20:00:00', 'capacity' => 10, 'available_seats' => 10]);
} catch (PDOException $e) {
    echo get_class($e) . ": " . $e->getMessage() . "\n";
}
