<?php
/*
 * Lab 05 – checker for Tasks 1 to 5.          Run from the backend folder:   php rest/routes/test_tasks.php
 *
 * The script starts PHP's built-in web server by itself (you do not need to start anything),
 * sends real HTTP requests to your API and prints the status code and the answer of every request.
 * Compare the output with the "Expected output" box of each task in the lab document.
 *
 * A section prints [TODO] until you write its routes.
 * Everything the script adds is deleted again, so you can run it as often as you like.
 *
 * DO NOT CHANGE THIS FILE.
 */
require_once __DIR__ . '/../dao/CategoryDao.php';
require_once __DIR__ . '/../dao/EventDao.php';
require_once __DIR__ . '/../dao/AnnouncementDao.php';

const PORT = 8765;           // the API under test
const PORT_DB_DOWN = 8766;   // the same API with a wrong database port (Task 2 – status 500)

$root = realpath(__DIR__ . '/../../..');   // the week-05 folder (server.php is there)

/* ---------- start / stop the built-in web server ---------- */

function start_server($root, $port, $env = [])
{
    $null = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
    $process = proc_open(
        [PHP_BINARY, '-S', "127.0.0.1:$port", 'server.php'],
        [0 => ['file', $null, 'r'], 1 => ['file', $null, 'w'], 2 => ['file', $null, 'w']],
        $pipes,
        $root,
        array_merge(getenv(), $env)
    );
    for ($i = 0; $i < 50; $i++) {                         // wait up to 5 seconds
        $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.1);
        if ($socket) {
            fclose($socket);
            return $process;
        }
        usleep(100000);
    }
    proc_terminate($process);
    exit("[ERROR] Could not start the web server on port $port. Is the port already in use?\n");
}

/* ---------- HTTP helpers ---------- */

/** Send a request and return [status code, decoded JSON (or the raw text if it is not JSON)]. */
function request($method, $path, $body = null, $port = PORT)
{
    $options = ['method' => $method, 'ignore_errors' => true, 'timeout' => 10, 'header' => "Accept: application/json\r\n"];
    if ($body !== null) {
        $options['header'] .= "Content-Type: application/json\r\n";
        $options['content'] = json_encode($body);
    }
    $text = @file_get_contents("http://127.0.0.1:$port/backend$path", false, stream_context_create(['http' => $options]));
    $headers = function_exists('http_get_last_response_headers') ? http_get_last_response_headers() : ($http_response_header ?? []);
    preg_match('/\s(\d{3})\s?/', $headers[0] ?? '', $match);
    $json = json_decode((string) $text, true);
    return [(int) ($match[1] ?? 0), $json === null ? (string) $text : $json];
}

/** A short, readable summary of an answer. */
function summary($answer)
{
    if (!is_array($answer)) {
        return 'not JSON: ' . mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags($answer))), 0, 60);
    }
    if (isset($answer['error'])) {
        return $answer['error'] . (array_key_exists('status', $answer) ? '' : '   [the JSON has no "status" field – Task 2.2]');
    }
    if (isset($answer['message'])) return $answer['message'];
    if (array_is_list($answer)) {
        $names = array_map(fn($row) => $row['title'] ?? $row['name'] ?? (isset($row['rating']) ? "rating {$row['rating']}" : '#' . ($row['id'] ?? '?')), $answer);
        return count($answer) . ' item(s)' . (count($answer) > 0 && count($answer) <= 5 ? ': ' . implode(', ', $names) : '');
    }
    return 'id ' . ($answer['id'] ?? '?') . ' – ' . ($answer['title'] ?? $answer['name'] ?? '');
}

/** Send a request and print one line:  POST /categories ........ 201  id 6 – Test Category */
function call($method, $path, $body = null, $port = PORT)
{
    [$status, $answer] = request($method, $path, $body, $port);
    echo str_pad("$method $path ", 60, '.') . " $status  " . summary($answer) . "\n";
    return [$status, $answer];
}

function title($text) { echo "\n=== $text ===\n"; }

/** A route that is not written yet answers with "Endpoint ... does not exist." (or with HTML before Task 2). */
function missing($method, $path, $body = null)
{
    [$status, $answer] = request($method, $path, $body);
    return $status === 404 && (!is_array($answer) || str_starts_with($answer['error'] ?? '', 'Endpoint '));
}

function todo($task, $what) { echo "[TODO] $task – $what is not implemented yet\n"; }

/* ---------- clean up what an earlier (interrupted) run left behind ---------- */

function clean_up()
{
    $db = BaseDao::connection();
    $db->exec("DELETE FROM categories WHERE name IN ('Checker Category', 'Checker Workshops')");
    $db->exec("DELETE FROM events WHERE title LIKE 'Checker Event%'");
    $db->exec("DELETE FROM announcements WHERE title LIKE 'Checker:%'");
}

clean_up();
$server = start_server($root, PORT);
$server_db_down = start_server($root, PORT_DB_DOWN, ['DB_HOST' => '127.0.0.1', 'DB_PORT' => '1']);

try {
    /* ================= Task 1 ================= */
    title('Task 1 – the API is running');
    call('GET', '/');
    call('GET', '/clubs');

    /* ================= Task 2 ================= */
    title('Task 2 – one error handler for the whole API');
    [$status, $answer] = request('GET', '/clubs/999');
    if (!is_array($answer)) {
        todo('Task 2.2', "Flight::map('error')");
    } else {
        call('GET', '/clubs/999');
        call('POST', '/clubs', ['name' => 'A']);
        call('GET', '/clubs', null, PORT_DB_DOWN);
        echo "    (the request above used a database that is not running)\n";
    }
    [$status, $answer] = request('GET', '/this-does-not-exist');
    if (!is_array($answer)) {
        todo('Task 2.3', "Flight::map('notFound')");
    } else {
        call('GET', '/this-does-not-exist');
        call('DELETE', '/clubs/abc?force=1');
    }

    /* ================= Task 3 ================= */
    title('Task 3 – routes for categories');
    if (missing('GET', '/categories')) {
        todo('Task 3.1', 'CategoryRoutes.php');
    } else {
        call('GET', '/categories');
        call('GET', '/categories/1');
        call('GET', '/categories/999');
        call('POST', '/categories', ['description' => 'no name']);
        call('POST', '/categories', ['name' => 'Music']);
        [$status, $created] = call('POST', '/categories', ['name' => 'Checker Category', 'description' => 'Added by the checker']);
        $id = $created['id'] ?? 0;
        call('PUT', "/categories/$id", ['name' => 'Checker Workshops']);
        call('PUT', '/categories/999', ['name' => 'Nothing']);
        call('DELETE', '/categories/1');
        call('DELETE', "/categories/$id");
        call('GET', "/categories/$id");
        echo "\n";
        if (missing('GET', '/categories/2/events')) {
            todo('Task 3.2', 'GET /categories/@id/events');
        } else {
            call('GET', '/categories/2/events');
            call('GET', '/categories/999/events');
        }
    }

    /* ================= Task 4 ================= */
    title('Task 4 – routes for events');
    if (missing('GET', '/events/1')) {
        todo('Task 4.1', 'the REST routes for /events');
    } else {
        call('GET', '/events');
        call('GET', '/events/7');
        call('GET', '/events/999');
        $event = [
            'title' => 'Checker Event', 'club_id' => 2, 'category_id' => 2, 'location' => 'Room B-103',
            'starts_at' => '2020-01-01 10:00:00', 'capacity' => 30, 'available_seats' => 999,
        ];
        call('POST', '/events', $event);
        $event['starts_at'] = date('Y-m-d', strtotime('+30 days')) . ' 18:00:00';
        [$status, $created] = call('POST', '/events', $event);
        $id = $created['id'] ?? 0;
        echo "    available_seats of the new event: " . ($created['available_seats'] ?? '?') . "\n";
        [$status, $updated] = call('PUT', "/events/$id", array_merge($event, ['capacity' => 40]));
        echo "    available_seats after the update: " . ($updated['available_seats'] ?? '?') . "\n";
        call('DELETE', "/events/$id");
    }

    echo "\n";
    [$status, $answer] = request('GET', '/events?search=night');
    if (missing('GET', '/events?search=night') || (is_array($answer) && array_is_list($answer) && count($answer) === 12)) {
        todo('Task 4.2', 'GET /events with ?search= and the filters');
    } else {
        call('GET', '/events?search=night');
        call('GET', '/events?search=a');
        call('GET', '/events?category_id=2&only_upcoming=1&min_free_seats=1');
        call('GET', '/events?club_id=999');
        call('GET', '/events?min_free_seats=-5');
    }

    echo "\n";
    if (missing('GET', '/events/9/reviews')) {
        todo('Task 4.3', 'the nested routes /events/@id/reviews and /clubs/@id/events');
    } else {
        call('GET', '/events/9/reviews');
        call('GET', '/events/999/reviews');
        call('GET', '/clubs/2/events');
        call('GET', '/clubs/999/events');
    }

    /* ================= Task 5 ================= */
    title('Task 5 – routes for announcements');
    if (missing('GET', '/announcements/1')) {
        todo('Task 5', 'AnnouncementRoutes.php');
    } else {
        call('GET', '/announcements');
        call('GET', '/announcements?limit=2');
        call('GET', '/announcements?limit=50');
        call('GET', '/announcements/1');
        call('GET', '/clubs/1/announcements');
        call('GET', '/clubs/999/announcements');
        call('POST', '/announcements', ['club_id' => 1, 'title' => 'Checker: second pin', 'body' => 'This one must not be saved.', 'is_pinned' => true]);
        [$status, $created] = call('POST', '/announcements', ['club_id' => 4, 'title' => 'Checker: debate on Friday', 'body' => 'Room B-103 at 18:00, everybody is welcome.', 'is_pinned' => true]);
        $id = $created['id'] ?? 0;
        call('PUT', "/announcements/$id", ['club_id' => 4, 'title' => 'Checker: debate moved to Monday', 'body' => 'Same room, same time, new day.', 'is_pinned' => true]);
        call('DELETE', "/announcements/$id");
        call('GET', "/announcements/$id");
    }
} finally {
    proc_terminate($server);
    proc_terminate($server_db_down);
    clean_up();
}
