<?php
/*
 * Slide 18 – Arrays: one structure, three shapes
 * Run:  php 08_arrays.php
 */
declare(strict_types=1);
header("Content-Type: text/plain; charset=utf-8");

echo "--- indexed (list) ---\n";
$cities = [
    "Sarajevo",
    "Mostar",
];
echo $cities[1], "\n";          // Mostar
$cities[] = "Tuzla";            // append
echo count($cities), " cities\n";
print_r($cities);

echo "\n--- associative (key => value) ---\n";
$venue = [
    "name"     => "Hall A",
    "capacity" => 300,
];
echo $venue["name"], "\n";      // Hall A
$venue["city"] = "Sarajevo";    // add a key
print_r($venue);

echo "\n--- list of associative arrays = rows of a table ---\n";
$events = [
    ["id" => 1, "title" => "Quiz",      "venue_id" => 2, "available_seats" => 12],
    ["id" => 2, "title" => "Hackathon", "venue_id" => 1, "available_seats" => 5],
];
echo $events[1]["title"], "\n"; // Hackathon

// The same data as a table: each row is an associative array, keys are column names
printf("%-4s %-10s %-9s %s\n", "id", "title", "venue_id", "available_seats");
foreach ($events as $row) {
    printf("%-4d %-10s %-9d %d\n", $row["id"], $row["title"], $row["venue_id"], $row["available_seats"]);
}

echo "\n--- useful array functions ---\n";
echo "keys:   ", implode(", ", array_keys($venue)), "\n";
echo "titles: ", implode(", ", array_column($events, "title")), "\n";
var_dump(isset($events[2]));                 // false – no third row
var_dump(array_key_exists("city", $venue));  // true
var_dump(in_array("Mostar", $cities, true)); // true

echo "\n--- var_dump shows the shape exactly ---\n";
var_dump($events[0]);
