<?php
/*
 * VERSION 2 demo – php rest/services/testv2.php
 * Rules exist, but every result has to be checked with ['success'].
 */
require_once __DIR__ . '/EventServicev2.php';

$service = new EventServicev2();
$next_month = date('Y-m-d H:i:s', strtotime('+30 days 18:00'));

$tests = [
    'Empty title' => ['title' => '', 'club_id' => 1, 'category_id' => 1, 'location' => 'Hall', 'starts_at' => $next_month, 'capacity' => 10],
    'Unknown club' => ['title' => 'Ghost Party', 'club_id' => 999, 'category_id' => 1, 'location' => 'Hall', 'starts_at' => $next_month, 'capacity' => 10],
    'Event in the past' => ['title' => 'Yesterday', 'club_id' => 1, 'category_id' => 1, 'location' => 'Hall', 'starts_at' => '2020-01-01 10:00:00', 'capacity' => 10],
];
foreach ($tests as $name => $event) {
    $result = $service->add($event);
    echo str_pad($name, 20) . ($result['success'] ? 'saved' : 'error: ' . $result['error']) . "\n";
}

$result = $service->add(['title' => 'Valid Event', 'club_id' => 1, 'category_id' => 1, 'location' => 'Hall',
                         'starts_at' => $next_month, 'capacity' => 30, 'available_seats' => 500]);
echo str_pad('Valid event', 20) . "saved with available_seats = " . $result['data']['available_seats'] . " (client sent 500)\n";
$service->delete($result['data']['id']);

// The weak spot: forget to check 'success' and you work with an error array as if it were an event
$event = $service->get_by_id(999);
echo "\nget_by_id(999) returned: ";
print_r($event);
echo "Which HTTP status should a route send for this? 400? 404? The array does not tell us.\n";
