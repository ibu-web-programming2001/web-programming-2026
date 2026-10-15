<?php
/*
 * VERSION 1 demo – run from the backend folder:   php rest/dao/testv1.php
 * Shows why gluing values into SQL is dangerous.
 * (EventDaov1 has the DB password hard-coded – change it there if yours is not empty.)
 */
header('Content-Type: text/plain'); // keeps the line breaks when opened in a browser; ignored on the CLI
require_once __DIR__ . '/EventDaov1.php';

$dao = new EventDaov1();

echo "Event #1:\n";
print_r($dao->get_by_id(1));

echo "\nSearch 'night' -> " . count($dao->search_by_title('night')) . " event(s)\n";

// --- SQL INJECTION ---
$evil_title = "' OR 1=1 -- ";
echo "Search \"$evil_title\" -> " . count($dao->search_by_title($evil_title)) . " event(s)   <- ALL events!\n";

$evil_id = "1 OR 1=1";
echo "get_by_id(\"$evil_id\") still returns a row – the attacker controls the query:\n";
print_r($dao->get_by_id($evil_id));

// The query that was executed:
echo "\nSQL that ran: SELECT * FROM events WHERE title LIKE '%$evil_title%'\n";
