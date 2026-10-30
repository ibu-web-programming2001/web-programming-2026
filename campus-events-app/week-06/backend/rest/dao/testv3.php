<?php
/*
 * VERSION 3 demo – php rest/dao/testv3.php
 * get_all / get_by_id are inherited from BaseDaov1 – ClubDaov3 has no own methods at all.
 */
require_once __DIR__ . '/EventDaov3.php';
require_once __DIR__ . '/ClubDaov3.php';

$event_dao = new EventDaov3();
$club_dao = new ClubDaov3();

echo "Events: " . count($event_dao->get_all()) . "\n";
echo "Clubs:  " . count($club_dao->get_all()) . "\n";
echo "Club #6: " . $club_dao->get_by_id(6)['name'] . "\n";

$event = $event_dao->add(['title' => 'Test Event', 'club_id' => 1, 'category_id' => 1, 'location' => 'Room A-1',
                          'starts_at' => '2027-01-15 18:00:00', 'capacity' => 20]);
echo "Added event id {$event['id']}\n";
$event_dao->delete($event['id']);
echo "Deleted again.\n";
