<?php
/*
 * Lab 03 – Task 8 (SOLUTION): CRUD test for AnnouncementDao.
 * Included at the end of test_tasks.php, so all DAOs are already loaded.
 */
$dao = new AnnouncementDao();

// 1. add – "hacker" is not a column, only_columns() must drop it
$created = $dao->add(['club_id' => 4, 'title' => 'Test announcement', 'body' => 'Created by my_tests.php', 'hacker' => 'DROP TABLE']);
echo "Added:     #{$created['id']} " . $dao->get_by_id($created['id'])['title'] . " (" . $dao->get_by_id($created['id'])['club_name'] . ")\n";

// 2. update
$dao->update(['title' => 'Updated announcement', 'is_pinned' => 1], $created['id']);
$updated = $dao->get_by_id($created['id']);
echo "Updated:   {$updated['title']}, pinned = {$updated['is_pinned']}\n";
echo "Pinned first in get_all(): " . ($dao->get_all()[0]['id'] == $created['id'] ? 'yes' : 'no') . "\n";

// 3. delete
$dao->delete($created['id']);
echo "Deleted:   get_by_id() returns " . var_export($dao->get_by_id($created['id']), true) . "\n";

// 4. a foreign key must stop an announcement for a club that does not exist
try {
    $dao->add(['club_id' => 999, 'title' => 'Ghost club', 'body' => 'Should fail']);
    echo "FK check:  NOT working – the row was inserted!\n";
} catch (PDOException $e) {
    echo "FK check:  refused (club 999 does not exist)\n";
}
