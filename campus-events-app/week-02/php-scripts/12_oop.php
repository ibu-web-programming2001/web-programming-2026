<?php
/*
 * Slide 23 – Object-oriented PHP: a class with constructor property promotion (PHP 8)
 * Run:  php 12_oop.php
 */
declare(strict_types=1);
header("Content-Type: text/plain; charset=utf-8");

class Event
{
    public function __construct(
        public readonly int $id,
        public string $title,
        private int $available_seats,
    ) {}

    public function has_seats(): bool
    {
        return $this->available_seats > 0;
    }

    public function take_seat(): void
    {
        if (!$this->has_seats()) {
            throw new RuntimeException("Sold out");
        }
        $this->available_seats--;
    }

    public function available_seats(): int
    {
        return $this->available_seats;
    }
}

echo "--- new, ->, methods ---\n";
$e = new Event(2, "Hackathon", 1);
echo "$e->title has {$e->available_seats()} seat(s)\n";
$e->take_seat();
var_dump($e->has_seats());   // false

echo "\n--- public: can be read and changed from outside ---\n";
$e->title = "Hackathon 2026";
echo $e->title, "\n";

echo "\n--- readonly: set once, then never changes ---\n";
try {
    $e->id = 99;
} catch (Error $ex) {
    echo get_class($ex), ": ", $ex->getMessage(), "\n";
}

echo "\n--- private: only the class itself can touch it ---\n";
try {
    echo $e->available_seats;
} catch (Error $ex) {
    echo get_class($ex), ": ", $ex->getMessage(), "\n";
}

echo "\n--- the rule lives in the class: take_seat() on a sold-out event ---\n";
try {
    $e->take_seat();
} catch (RuntimeException $ex) {
    echo "RuntimeException: ", $ex->getMessage(), "\n";
}

echo "\n--- :: static members (like Flight::route()) ---\n";
class Config
{
    public const MAX_UPCOMING = 3;
    private static int $instances = 0;

    public static function count_instance(): int
    {
        return ++self::$instances;
    }
}
echo Config::MAX_UPCOMING, "\n";        // 3
echo Config::count_instance(), "\n";    // 1
echo Config::count_instance(), "\n";    // 2

echo "\n--- objects in var_dump / json_encode ---\n";
var_dump($e);                // private property is shown with ["available_seats":"Event":private]
echo json_encode($e), "\n";  // only public properties: {"id":2,"title":"Hackathon 2026"}
