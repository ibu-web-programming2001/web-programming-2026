<?php
/*
 * VERSION 2 demo – php rest/dao/testv2.php
 * Prepared statements: the same attack does not work any more. Plus full CRUD.
 */
require_once __DIR__ . '/EventDaov2.php';

$dao = new EventDaov2();

$evil_title = "' OR 1=1 -- ";
echo "Search \"$evil_title\" -> " . count($dao->search_by_title($evil_title)) . " event(s)   <- safe\n";

echo "\n--- CRUD ---\n";
$id = $dao->add('Test Event', 1, 1, 'Room A-1', '2027-01-15 18:00:00', 20);
echo "Added event with id $id\n";
print_r($dao->get_by_id($id));

$dao->update($id, 'Test Event (moved to a bigger room)', 50);
echo "Updated:\n";
print_r($dao->get_by_id($id));

$dao->delete($id);
echo "Deleted, get_by_id now returns: ";
var_dump($dao->get_by_id($id));
