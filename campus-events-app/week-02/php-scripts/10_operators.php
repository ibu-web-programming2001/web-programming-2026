<?php
/*
 * Slide 20 – Operators: the usual ones plus ??, ?-> and <=>
 * Run:  php 10_operators.php
 */
declare(strict_types=1);
header("Content-Type: text/plain; charset=utf-8");

echo "--- arithmetic ---\n";
echo 7 + 2, " ", 7 - 2, " ", 7 * 2, " ", 7 / 2, " ", 7 % 2, " ", 2 ** 10, "\n"; // 9 5 14 3.5 1 1024
echo intdiv(7, 2), "\n";                                                        // 3

echo "\n--- comparison and logical ---\n";
$left = 5;
var_dump($left > 0 && $left <= 5);   // true
var_dump($left === 0 || $left > 10); // false
var_dump(!($left === 0));            // true

echo "\n--- assignment shortcuts ---\n";
$seats = 10;
$seats -= 1;                 // 9
$seats += 2;                 // 11
$msg = "Seats: ";
$msg .= $seats;              // "Seats: 11"
echo $msg, "\n";
$page = null;
$page ??= 1;                 // assign only if null
echo "page $page\n";         // page 1

echo "\n--- ?? null coalescing ---\n";
$_GET = ["club_id" => "2"];                    // simulate ?club_id=2
$page    = $_GET["page"] ?? 1;                 // key missing -> default
$club_id = $_GET["club_id"] ?? null;
echo "page=$page club_id=$club_id\n";          // page=1 club_id=2
// Compare with the old way: isset($_GET["page"]) ? $_GET["page"] : 1

echo "\n--- ?-> nullsafe ---\n";
class City    { public function __construct(public string $name) {} }
class Faculty { public function __construct(public ?City $city) {} }
class User    { public function __construct(public ?Faculty $faculty) {} }

$user_with    = new User(new Faculty(new City("Sarajevo")));
$user_without = new User(null);

var_dump($user_with?->faculty?->city?->name);      // "Sarajevo"
var_dump($user_without?->faculty?->city?->name);   // NULL, no crash

// Without ?-> PHP keeps going but complains twice:
//   Warning: Attempt to read property "city" on null
//   Warning: Attempt to read property "name" on null
echo "without ?->: ";
var_dump($user_without->faculty->city->name);      // NULL + two warnings
// and the old-style safe version needs three checks:
$city = $user_without->faculty !== null && $user_without->faculty->city !== null
    ? $user_without->faculty->city->name
    : null;
var_dump($city);

echo "\n--- <=> spaceship: -1, 0 or 1 ---\n";
var_dump(1 <=> 2, 2 <=> 2, 3 <=> 2);
var_dump("2026-03-01" <=> "2026-02-01");           // 1 – dates as Y-m-d strings sort correctly

$events = [
    ["title" => "Hackathon",   "starts_at" => "2026-11-20 10:00"],
    ["title" => "Quiz",        "starts_at" => "2026-10-28 18:00"],
    ["title" => "Movie Night", "starts_at" => "2026-11-05 20:00"],
];
usort($events, fn($a, $b) => $a["starts_at"] <=> $b["starts_at"]);   // by date
foreach ($events as $e) {
    echo "{$e['starts_at']}  {$e['title']}\n";
}
