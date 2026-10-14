<?php
/*
 * Slide 19 – Arrays to JSON: json_encode (response) and json_decode (request body)
 *
 * Run in the terminal:   php 09_json.php
 * Run as an API:         php -S localhost:8000   then in Postman:
 *                        POST http://localhost:8000/09_json.php   body (raw, JSON): {"event_id": 2}
 */
declare(strict_types=1);

$is_cli = PHP_SAPI === "cli";

// --- PHP array -> JSON (the response) ---------------------------------------
$event = [
    "id"              => 2,
    "title"           => "Hackathon",
    "available_seats" => 5,
    "is_free"         => true,
];

if ($is_cli) {
    echo "--- json_encode: PHP array -> JSON ---\n";
    echo json_encode($event), "\n";
    // {"id":2,"title":"Hackathon","available_seats":5,"is_free":true}

    echo "\n--- pretty printed ---\n";
    echo json_encode($event, JSON_PRETTY_PRINT), "\n";

    echo "\n--- associative array -> object, list -> array ---\n";
    echo json_encode(["Quiz", "Hackathon"]), "\n";            // ["Quiz","Hackathon"]
    echo json_encode([["id" => 1], ["id" => 2]]), "\n";       // [{"id":1},{"id":2}]
    echo json_encode([]), "\n";                               // []  (empty array is a list!)
    echo json_encode(new stdClass()), "\n";                   // {}
    echo json_encode(["price" => 9.5, "note" => null]), "\n"; // {"price":9.5,"note":null}
    echo json_encode("Čevapi ćevapi"), "\n";                  // unicode escaped by default
    echo json_encode("Čevapi ćevapi", JSON_UNESCAPED_UNICODE), "\n";

    // --- JSON -> PHP array (the request body) -------------------------------
    echo "\n--- json_decode: JSON -> PHP array ---\n";
    $body = '{"event_id": 2}';          // on the server this comes from php://input
    $data = json_decode($body, true);   // true = associative array instead of stdClass
    echo $data["event_id"], "\n";       // 2
    var_dump($data);

    echo "\n--- without true you get an object ---\n";
    $obj = json_decode($body);
    echo $obj->event_id, "\n";          // 2

    echo "\n--- broken JSON ---\n";
    var_dump(json_decode("{bad json", true));          // NULL
    echo json_last_error_msg(), "\n";                  // Syntax error
    try {
        json_decode("{bad json", true, flags: JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        echo "JsonException: ", $e->getMessage(), "\n";
    }
    exit;
}

// --- Web mode: a tiny JSON API (use Postman) --------------------------------
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    echo json_encode($event);           // GET  -> the event as JSON
    exit;
}

// POST: read the JSON body, e.g. {"event_id": 2}
$body = file_get_contents("php://input");
try {
    $data = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON: " . $e->getMessage()]);
    exit;
}

$event_id = $data["event_id"] ?? null;
if (!is_int($event_id) || $event_id < 1) {
    http_response_code(400);
    echo json_encode(["error" => "event_id must be a positive integer"]);
    exit;
}

http_response_code(201);
echo json_encode([
    "message"  => "Registered for event $event_id",
    "received" => $data,
]);
