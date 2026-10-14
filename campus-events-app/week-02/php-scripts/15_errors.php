<?php
/*
 * Slide 26 – When it breaks: errors, exceptions and debugging
 * Run:  php 15_errors.php
 */
declare(strict_types=1);

// development only – never on a production server
ini_set("display_errors", "1");
error_reporting(E_ALL);

require __DIR__ . "/Event.php";   // the Event class from slide 23 (same code as in 12_oop.php)

$event = new Event(2, "Hackathon", 1);

echo "--- try / catch / finally ---\n";
foreach ([1, 2] as $attempt) {
    echo "attempt $attempt: ";
    try {
        $event->take_seat();
        echo json_encode(["message" => "seat taken"]), "\n";
    } catch (RuntimeException $e) {
        if (PHP_SAPI !== "cli") {
            http_response_code(400);                   // on the web: 400 Bad Request
        }
        echo json_encode(["error" => $e->getMessage()]), "\n";
    } finally {
        error_log("Seat request handled");             // server log (stderr in the terminal)
    }
}

echo "\n--- an exception that nobody catches = fatal error ---\n";
echo "(uncomment the line in the script to see the stack trace)\n";
// $event->take_seat();

echo "\n--- warnings are not exceptions: PHP prints them and goes on ---\n";
$events = [];
$first = $events[0] ?? null;      // safe
echo "with ??: ", var_export($first, true), "\n";
$first = @$events[0];             // @ hides the warning – do not do this
$first = $events[0];              // Warning: Undefined array key 0 (script continues)
echo "script continues after the warning\n";

echo "\n--- errors ARE catchable since PHP 7 (Error, TypeError, DivisionByZeroError) ---\n";
try {
    echo intdiv(10, 0);
} catch (DivisionByZeroError $e) {
    echo get_class($e), ": ", $e->getMessage(), "\n";
}

echo "\n--- custom exceptions: the rule carries its own HTTP status (week 4 preview) ---\n";
class ValidationException extends Exception
{
    public function __construct(string $message, public readonly int $status = 400)
    {
        parent::__construct($message);
    }
}
try {
    throw new ValidationException("Max. 3 upcoming registrations", 422);
} catch (ValidationException $e) {
    echo "status {$e->status}: {$e->getMessage()}\n";
}

echo "\n--- debugging: read the error, var_dump, print_r ---\n";
$row = ["id" => 2, "title" => "Hackathon", "available_seats" => 0];
print_r($row);
var_dump($row["available_seats"] == false);    // true  – careless check
var_dump($row["available_seats"] === 0);       // true  – what you meant
// Read the stack trace from the top: the first line is where it was thrown,
// the next lines are who called it (route -> service -> dao in Part 2).
function register(int $upcoming): void
{
    if ($upcoming >= 3) {
        throw new ValidationException("Max. 3 upcoming registrations");
    }
}
function handle_request(): void
{
    register(3);
}
try {
    handle_request();
} catch (ValidationException $e) {
    echo "\n", get_class($e), ": ", $e->getMessage(), "\n";
    echo "  in ", basename($e->getFile()), " line ", $e->getLine(), "\n";
    echo $e->getTraceAsString(), "\n";
}
