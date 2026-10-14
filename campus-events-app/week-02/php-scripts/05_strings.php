<?php
/*
 * Slide 14 – Strings: interpolation ("..."), literal ('...'), concatenation (.)
 * Run:  php 05_strings.php
 */
declare(strict_types=1);
header("Content-Type: text/plain; charset=utf-8");

$name  = "Ana";
$left  = 5;
$event = ["title" => "Hackathon", "venue" => "Hall A"];

echo "Hi $name, $left seats left", "\n";  // Hi Ana, 5 seats left
echo 'Hi $name', "\n";                    // Hi $name
echo "Hi " . $name . "!", "\n";           // Hi Ana!
echo "Venue: {$event['venue']}", "\n";    // Venue: Hall A

echo "\n--- built-in string functions ---\n";
echo strlen("Hackathon"), "\n";                 // 9
echo strtoupper("hackathon"), "\n";             // HACKATHON
echo sprintf("%d/%d seats", 115, 120), "\n";    // 115/120 seats
var_dump(str_contains("Hackathon", "Hack"));    // bool(true)

echo "\n--- a few more you will need ---\n";
echo trim("   ana@campusevents.ba  "), "|\n";   // removes spaces around input
echo ucfirst("hackathon"), "\n";                // Hackathon
echo str_replace("Hall", "Room", "Hall A"), "\n"; // Room A
echo substr("Hackathon 2026", 0, 9), "\n";      // Hackathon
echo implode(", ", ["tech", "student"]), "\n";  // tech, student
print_r(explode("@", "ana@campusevents.ba"));   // [ana, campusevents.ba]

// Why "+ on strings is a bug": PHP tries to turn both sides into numbers
echo "\n--- + is not concatenation ---\n";
echo "5" + "3", "\n";       // 8 (arithmetic, not "53")
echo "5" . "3", "\n";       // 53
try {
    echo "Hi " + "Ana";     // TypeError: Unsupported operand types / non-numeric value
} catch (TypeError $e) {
    echo "TypeError: ", $e->getMessage(), "\n";
}
