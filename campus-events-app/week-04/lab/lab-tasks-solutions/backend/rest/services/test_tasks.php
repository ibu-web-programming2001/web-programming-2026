<?php
/*
 * Lab 04 – checker for Tasks 2–8.     Run from the backend folder:   php rest/services/test_tasks.php
 *
 * Every section prints [TODO] until you write the code it needs.
 * Compare your output with the "Expected output" boxes in the Lab Tasks document.
 * The script cleans up after itself, so you can run it as often as you like.
 */
require_once __DIR__ . '/CategoryService.php';
require_once __DIR__ . '/ClubService.php';
require_once __DIR__ . '/EventService.php';
require_once __DIR__ . '/RegistrationService.php';
require_once __DIR__ . '/ReviewService.php';
require_once __DIR__ . '/UserService.php';

function title($text) { echo "\n=== $text ===\n"; }

/** Thrown by the "throw new LogicException('TODO ...')" lines of the skeleton. */
function is_todo($e) { return $e instanceof LogicException && str_starts_with($e->getMessage(), 'TODO'); }

/** Run one action and print OK or the exception it threw. A TODO stops the whole section. */
function attempt($description, $action)
{
    $line = str_pad($description, 50, '.') . ' ';
    try {
        $result = $action();
        echo $line . "OK" . (is_array($result) && isset($result['id']) ? " (id {$result['id']})" : '') . "\n";
        return $result;
    } catch (HttpException $e) {
        echo $line . get_class($e) . " [{$e->get_status()}]: {$e->getMessage()}\n";
    } catch (Throwable $e) {
        if (is_todo($e)) {
            throw $e;
        }
        echo $line . "[ERROR] " . get_class($e) . ": {$e->getMessage()}\n";
    }
    return null;
}

/** Run one section; a TODO skips the rest of the section. */
function section($name, $code)
{
    title($name);
    try {
        $code();
    } catch (LogicException $e) {
        echo is_todo($e) ? "[TODO] " . substr($e->getMessage(), 5) . " is not implemented yet\n" : "[ERROR] {$e->getMessage()}\n";
    }
}

$category_dao = new CategoryDao();
$club_dao = new ClubDao();
$event_dao = new EventDao();
$registration_dao = new RegistrationDao();
$review_dao = new ReviewDao();
$user_dao = new UserDao();

/* ------------------------------------------------------------------ */
section('Task 2 – Validator', function () {
    if (!method_exists('Validator', 'in_future') || !method_exists('Validator', 'no_html')) {
        throw new LogicException('TODO Task 2 – Validator::in_future() / no_html()');
    }
    attempt('in_future("2020-01-01 10:00:00")', fn() => Validator::in_future('2020-01-01 10:00:00', 'starts_at'));
    attempt('in_future(tomorrow)', fn() => Validator::in_future(date('Y-m-d H:i:s', strtotime('+1 day')), 'starts_at'));
    attempt('no_html("<script>alert(1)</script>")', fn() => Validator::no_html('<script>alert(1)</script>', 'comment'));
    attempt('no_html("Great event!")', fn() => Validator::no_html('Great event!', 'comment'));
});

/* ------------------------------------------------------------------ */
section('Task 3 – CategoryService', function () use ($category_dao) {
    $categories = new CategoryService();
    $old = $category_dao->get_by_name('Test Category');
    if ($old) $category_dao->delete($old['id']);

    attempt('add: no name', fn() => $categories->add(['description' => 'x']));
    attempt('add: name "M"', fn() => $categories->add(['name' => 'M']));
    attempt('add: name "Music" (exists)', fn() => $categories->add(['name' => 'Music']));
    attempt('update #1: keeps its own name "Music"', fn() => $categories->update(['name' => 'Music', 'description' => 'Concerts, open mics and jam sessions'], 1));
    $new = attempt('add: "Test Category"', fn() => $categories->add(['name' => 'Test Category', 'description' => '']));
    attempt('delete #1 (Music has events)', fn() => $categories->delete(1));
    attempt('delete #999', fn() => $categories->delete(999));
    if ($new) {
        attempt('delete the new category (no events)', fn() => $categories->delete($new['id']));
    }
    $left = $category_dao->get_by_name('Test Category');
    if ($left) $category_dao->delete($left['id']);
});

/* ------------------------------------------------------------------ */
section('Task 4 – EventService', function () use ($event_dao) {
    $events = new EventService();
    $data = ['title' => 'Checker Test Event', 'club_id' => 2, 'category_id' => 2, 'location' => 'Room B-103',
             'starts_at' => date('Y-m-d', strtotime('+20 days')) . 'T18:00', 'capacity' => 30, 'available_seats' => 999];
    foreach ($event_dao->get_all() as $e) {
        if ($e['title'] === 'Checker Test Event') $event_dao->delete($e['id']);
    }

    attempt('add: starts in 2020', fn() => $events->add(array_merge($data, ['starts_at' => '2020-01-01 10:00'])));
    $new = attempt('add: valid event, client sends 999 seats', fn() => $events->add($data));
    try {
        if ($new) {
            echo "    available_seats of the new event: {$new['available_seats']}\n";
            $robot = $event_dao->get_by_id(7);
            attempt('update #7 Robot Build Day: capacity 2 -> 1', fn() => $events->update(
                ['title' => $robot['title'], 'club_id' => 6, 'category_id' => 2, 'location' => $robot['location'],
                 'starts_at' => $robot['starts_at'], 'capacity' => 1], 7));
            $updated = attempt('update the new event: capacity 30 -> 40', fn() => $events->update(array_merge($data, ['capacity' => 40]), $new['id']));
            if ($updated) {
                echo "    available_seats after the update: {$updated['available_seats']}\n";
            }
        }
    } finally {
        if ($new) $event_dao->delete($new['id']);
    }
});

/* ------------------------------------------------------------------ */
section('Task 5 – ReviewService', function () use ($registration_dao, $review_dao) {
    $reviews = new ReviewService();
    attempt('update review #1: rating 7', fn() => $reviews->update(['rating' => 7], 1));
    attempt('update review #1: comment with HTML', fn() => $reviews->update(['rating' => 5, 'comment' => '<b>wow</b>'], 1));
    attempt('add: event #999', fn() => $reviews->add(['user_id' => 3, 'event_id' => 999, 'rating' => 4]));
    attempt('add: Ema reviews Open Mic Night again', fn() => $reviews->add(['user_id' => 2, 'event_id' => 9, 'rating' => 3]));
    attempt('add: Tarik reviews Open Mic Night (not there)', fn() => $reviews->add(['user_id' => 3, 'event_id' => 9, 'rating' => 4]));

    // Tarik attended the Welcome Concert (registration #4) – only for this test
    $registration_dao->update(['status' => 'attended'], 4);
    try {
        $new = attempt('add: Tarik reviews the Welcome Concert', fn() => $reviews->add(['user_id' => 3, 'event_id' => 1, 'rating' => 5, 'comment' => '']));
    } finally {
        $own = $review_dao->get_by_user_and_event(3, 1);
        if ($own) $review_dao->delete($own['id']);
        $registration_dao->update(['status' => 'registered'], 4);
    }
});

/* ------------------------------------------------------------------ */
section('Task 6 – RegistrationService::register()', function () use ($event_dao) {
    $registrations = new RegistrationService();
    attempt('Ema (3 upcoming) registers for Movie Night', fn() => $registrations->register(2, 5));
    attempt('Tarik registers for sold-out Robot Build Day', fn() => $registrations->register(3, 7));
    attempt('Tarik registers for the Welcome Concert again', fn() => $registrations->register(3, 1));
    attempt('Tarik registers for the past Open Mic Night', fn() => $registrations->register(3, 9));
    attempt('student #999 registers for Chess Blitz Night', fn() => $registrations->register(999, 12));
    attempt('Tarik registers for event #999', fn() => $registrations->register(3, 999));
    $new = attempt('Tarik registers for Chess Blitz Night', fn() => $registrations->register(3, 12));
    if ($new) {
        echo "    free seats now: " . $event_dao->get_by_id(12)['available_seats'] . "\n";
        $registrations->delete($new['id']);   // clean up – gives the seat back
        echo "    free seats after the clean-up: " . $event_dao->get_by_id(12)['available_seats'] . "\n";
    }
});

/* ------------------------------------------------------------------ */
section('Task 7 – UserService::add()', function () use ($user_dao) {
    $users = new UserService();
    $old = $user_dao->get_by_email('test.student@campusevents.ba');
    if ($old) $user_dao->delete($old['id']);
    $data = ['first_name' => 'Test', 'last_name' => 'Student', 'email' => 'Test.Student@CampusEvents.ba', 'faculty' => 'FENS'];

    attempt('add: no password', fn() => $users->add($data));
    attempt('add: password "123"', fn() => $users->add($data + ['password' => '123']));
    attempt('add: email "Ema@CampusEvents.ba" (exists)', fn() => $users->add(array_merge($data, ['email' => 'Ema@CampusEvents.ba', 'password' => 'Secret123'])));
    $new = attempt('add: valid student', fn() => $users->add($data + ['password' => 'Secret123']));
    try {
        if ($new) {
            $stored = $user_dao->get_by_email('test.student@campusevents.ba');
            echo "    stored email:      " . ($stored['email'] ?? '(not found – was the email saved in lowercase?)') . "\n";
            echo "    role:              " . ($stored['role'] ?? '-') . "\n";
            echo "    password is hashed: " . ($stored && password_verify('Secret123', $stored['password']) ? 'yes' : 'NO') . "\n";
            echo "    hash returned by add(): " . (array_key_exists('password', $new) ? 'YES (it must not be)' : 'no') . "\n";
        }
    } finally {
        $left = $user_dao->get_by_email('test.student@campusevents.ba');
        if ($left) $user_dao->delete($left['id']);
    }
});

/* ------------------------------------------------------------------ */
title('Task 8 – your own tests');
require __DIR__ . '/my_tests.php';
