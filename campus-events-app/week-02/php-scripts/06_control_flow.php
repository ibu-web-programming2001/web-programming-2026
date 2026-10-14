<?php
/*
 * Slide 15 – Control flow: if / elseif / else, switch, match (PHP 8)
 * Run:  php 06_control_flow.php
 */
declare(strict_types=1);
header("Content-Type: text/plain; charset=utf-8");

echo "--- if / elseif / else ---\n";
foreach ([0, 3, 40] as $left) {
    if ($left === 0) {
        $badge = "Sold out";
    } elseif ($left <= 5) {
        $badge = "$left left!";
    } else {
        $badge = "Open";
    }
    echo "available_seats = $left  ->  $badge\n";
}

echo "\n--- switch (classic) ---\n";
foreach (["registered", "cancelled", "attended"] as $status) {
    switch ($status) {
        case "registered":
            $label = "Going";
            break;
        case "cancelled":
            $label = "Cancelled";
            break;
        default:
            $label = "Unknown";
    }
    echo "$status -> $label\n";
}

echo "\n--- match (PHP 8) ---\n";
foreach (["registered", "cancelled", "attended", "waiting"] as $status) {
    $label = match ($status) {
        "registered" => "Going",
        "cancelled"  => "Cancelled",
        "attended"   => "Attended",
        default      => "Unknown",
    };
    echo "$status -> $label\n";
}

// match compares with === and never falls through
echo "\n--- match uses ===, switch uses == ---\n";
$code = "1";                       // a string, e.g. from $_GET
switch ($code) {
    case 1:  echo "switch: matched int 1 (loose ==)\n"; break;
    default: echo "switch: no match\n";
}
echo match ($code) {
    1       => "match: matched int 1\n",
    default => "match: no match – \"1\" !== 1\n",
};

// Without a default, match throws when nothing matches
try {
    echo match ($code) { "2" => "two" };
} catch (\UnhandledMatchError $e) {
    echo "UnhandledMatchError: ", $e->getMessage(), "\n";
}
