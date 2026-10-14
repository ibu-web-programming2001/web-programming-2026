<?php
/*
 * Slides 16–17 – Loops: foreach, for, while, continue and break
 * Run:  php 07_loops.php
 */
declare(strict_types=1);
header("Content-Type: text/plain; charset=utf-8");

echo "--- foreach, step by step (slide 16) ---\n";
$events = ["Hackathon", "Movie Night", "Chess Cup"];

foreach ($events as $i => $title) {
    echo "$i: $title\n";
}

echo "\n--- foreach over rows from the DAO ---\n";
$rows = [
    ["id" => 1, "title" => "Quiz",      "available_seats" => 12],
    ["id" => 2, "title" => "Hackathon", "available_seats" => 5],
    ["id" => 3, "title" => "Chess Cup", "available_seats" => 0],
];
foreach ($rows as $event) {
    echo "{$event['id']}: {$event['title']} ({$event['available_seats']} seats)\n";
}

echo "\n--- for: known count ---\n";
for ($i = 1; $i <= 3; $i++) {
    echo "Seat $i\n";
}

echo "\n--- while: until false ---\n";
$left = 3;
while ($left > 0) {
    echo "$left left\n";
    $left--;
}

echo "\n--- continue skips one pass, break stops the loop ---\n";
$events = [
    ["title" => "Quiz",        "available_seats" => 12],
    ["title" => "Chess Cup",   "available_seats" => 0],
    ["title" => "Hackathon",   "available_seats" => 5],
    ["title" => "Movie Night", "available_seats" => 40],
    ["title" => "Career Day",  "available_seats" => 100],
    ["title" => "Game Jam",    "available_seats" => 8],
];

$max = $events[0]["available_seats"];
$title = $events[0]["title"];
foreach ($events as $event) {
    if ($event["available_seats"] > $max) { 
        $max = $event["available_seats"];
        $title = $event["title"];
    }
}
echo "The event with the most available seats is $title ($max seats)\n\n";

$open = [];
foreach ($events as $event) {
    if ($event["available_seats"] === 0) {
        echo "skip  {$event['title']} (sold out)\n";
        continue;      // skip sold-out events
    }
    $open[] = $event;
    echo "keep  {$event['title']}\n";
    if (count($open) === 3) {
        echo "stop  three are enough\n";
        break;         // three are enough
    }
}
echo "\nOpen events: ", implode(", ", array_column($open, "title")), "\n";
