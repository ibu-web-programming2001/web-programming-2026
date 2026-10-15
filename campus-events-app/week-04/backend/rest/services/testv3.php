<?php
/*
 * VERSION 3 (FINAL) demo – run from the backend folder:
 *
 *     php rest/services/testv3.php
 *
 * Services throw exceptions (with an HTTP status) when a rule is broken.
 *
 * Every block tries something that breaks a rule and prints the message of the exception.
 * Re-import database/campusevents.sql afterwards if you want the original sample data back.
 */
header('Content-Type: text/plain'); // keeps the line breaks when opened in a browser; ignored on the CLI
require_once __DIR__ . '/CategoryService.php';
require_once __DIR__ . '/ClubService.php';
require_once __DIR__ . '/EventService.php';
require_once __DIR__ . '/RegistrationService.php';
require_once __DIR__ . '/ReviewService.php';
require_once __DIR__ . '/UserService.php';
require_once __DIR__ . '/DashboardService.php';

function attempt($description, $action)
{
    echo str_pad($description, 55, '.') . ' ';
    try {
        $result = $action();
        echo "OK" . (is_array($result) && isset($result['id']) ? " (id {$result['id']})" : '') . "\n";
        return $result;
    } catch (HttpException $e) {
        echo get_class($e) . " [{$e->get_status()}]: {$e->getMessage()}\n";
    }
}

$categories = new CategoryService();
$clubs = new ClubService();
$events = new EventService();
$registrations = new RegistrationService();
$reviews = new ReviewService();
$users = new UserService();

echo "\n--- Validation ---\n";
attempt('Category without a name', fn() => $categories->add(['description' => 'x']));
attempt('Duplicate category name', fn() => $categories->add(['name' => 'Music']));
attempt('Club founded in the future', fn() => $clubs->add(['name' => 'Time Travel Club', 'founded_year' => 3000]));
attempt('Event with a wrong date format', fn() => $events->add(['title' => 'Party', 'club_id' => 1, 'category_id' => 1, 'location' => 'Hall', 'starts_at' => '20.11.2026', 'capacity' => 10]));
attempt('Event in the past', fn() => $events->add(['title' => 'Party', 'club_id' => 1, 'category_id' => 1, 'location' => 'Hall', 'starts_at' => '2020-01-01 10:00', 'capacity' => 10]));
attempt('Event of a non-existing club', fn() => $events->add(['title' => 'Party', 'club_id' => 999, 'category_id' => 1, 'location' => 'Hall', 'starts_at' => '2027-01-01T20:00', 'capacity' => 10]));
attempt('User with a weak password', fn() => $users->add(['first_name' => 'Test', 'last_name' => 'User', 'email' => 'test@campusevents.ba', 'password' => '123']));
attempt('Review with rating 7', fn() => $reviews->add(['user_id' => 3, 'event_id' => 9, 'rating' => 7]));
attempt('Second review of the same event', fn() => $reviews->add(['user_id' => 2, 'event_id' => 9, 'rating' => 3]));

echo "\n--- Not found ---\n";
attempt('Get event #999', fn() => $events->get_by_id(999));

echo "\n--- Delete rules ---\n";
attempt('Delete a club that has events', fn() => $clubs->delete(1));
attempt('Delete a category with events', fn() => $categories->delete(1));
attempt('Delete an event with registrations', fn() => $events->delete(1));
attempt('Capacity of Robot Build Day 2 -> 1', fn() => $events->update(['title' => 'Robot Build Day', 'club_id' => 6, 'category_id' => 2, 'location' => 'Robotics Lab', 'starts_at' => $events->get_by_id(7)['starts_at'], 'capacity' => 1], 7));

echo "\n--- Registrations ---\n";
attempt('Ema (3 upcoming registrations) registers again', fn() => $registrations->register(2, 5));
attempt('Tarik registers for sold-out Robot Build Day', fn() => $registrations->register(3, 7));
attempt('Tarik registers for the Welcome Concert twice', fn() => $registrations->register(3, 1));
attempt('Tarik registers for the past Open Mic Night', fn() => $registrations->register(3, 9));
$registration = attempt('Tarik registers for the Chess Blitz Night', fn() => $registrations->register(3, 12));
if ($registration) {
    echo "    free seats now: " . $events->get_by_id(12)['available_seats'] . "\n";
    attempt('Tarik cancels it', fn() => $registrations->cancel($registration['id']));
    echo "    free seats now: " . $events->get_by_id(12)['available_seats'] . "\n";
    attempt('Cancel the same registration again', fn() => $registrations->cancel($registration['id']));
    $registrations->delete($registration['id']);   // clean up
}

echo "\n--- Dashboard (combines several DAOs) ---\n";
$stats = (new DashboardService())->get_stats();
echo "Events: {$stats['events']} ({$stats['upcoming_events']} upcoming), students: {$stats['students']}, "
   . "upcoming registrations: {$stats['upcoming_registrations']}, attended: {$stats['attended']}\n";
