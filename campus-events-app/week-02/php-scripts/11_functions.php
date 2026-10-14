<?php
/*
 * Slide 22 – Functions: small, typed, named for what they do
 * Run:  php 11_functions.php
 */
declare(strict_types=1);
header("Content-Type: text/plain; charset=utf-8");

/** Can the student register for one more event? */
function can_register(
    int $upcoming,
    int $max = 3            // default value
): bool {
    return $upcoming < $max;
}

var_dump(can_register(2));          // true
var_dump(can_register(3));          // false
var_dump(can_register(3, max: 5));  // true: named argument

echo "\n--- return types: void, ?type, union ---\n";
function log_line(string $msg): void { echo "[log] $msg\n"; }
function find_event(array $events, int $id): ?array {        // ?array = array or null
    foreach ($events as $e) {
        if ($e["id"] === $id) { return $e; }
    }
    return null;
}
log_line("looking for event 2");
$events = [
    ["id" => 1, "title" => "Quiz",      "available_seats" => 12],
    ["id" => 2, "title" => "Hackathon", "available_seats" => 5],
    ["id" => 3, "title" => "Chess Cup", "available_seats" => 0],
];
var_dump(find_event($events, 2)["title"] ?? null);  // "Hackathon"
var_dump(find_event($events, 9));                   // NULL

echo "\n--- arrow function: one expression, no braces ---\n";
$titles = array_map(fn($e) => $e["title"], $events);
print_r($titles);

echo "\n--- built-ins you will use ---\n";
echo count($events), "\n";                                         // 3
var_dump(isset($events[0]["title"]));                              // true
var_dump(in_array("Quiz", $titles, true));                         // true
$open = array_filter($events, fn($e) => $e["available_seats"] > 0); // keep matching ones
print_r(array_column($open, "title"));                             // Quiz, Hackathon
echo date("Y-m-d H:i"), "\n";                                       // now, as text

echo "\n--- array_filter keeps the keys; reindex with array_values ---\n";
$sold_out = array_filter($events, fn($e) => $e["available_seats"] === 0);
echo json_encode($sold_out), "\n";                // {"2":{...}}  <- JSON object, not a list!
echo json_encode(array_values($sold_out)), "\n";  // [{...}]      <- what the API should return

echo "\n--- closures capture variables with use ---\n";
$min_seats = 5;
$enough = array_filter($events, function ($e) use ($min_seats) {
    return $e["available_seats"] >= $min_seats;
});
print_r(array_column($enough, "title"));
