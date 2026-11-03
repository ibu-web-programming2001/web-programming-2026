<?php
/*
 * Task 8 – your own tests for ClubService.
 * test_tasks.php includes this file at the end, so all services, DAOs and the attempt() helper are already loaded.
 * Run:  php rest/services/test_tasks.php
 *
 * Your tests must delete everything they add.
 */
$clubs = new ClubService();
$club_dao = new ClubDao();
$old = $club_dao->get_by_name('Chess Club');
if ($old) $club_dao->delete($old['id']);

// 8.1 – a club without a name
attempt('add: club without a name', fn() => $clubs->add(['faculty' => 'FENS']));

// 8.2 – a club founded in the future
attempt('add: club founded in 3000', fn() => $clubs->add(['name' => 'Time Travel Club', 'founded_year' => 3000]));

// 8.3 – a name that already exists
attempt('add: club "Robotics Club" (exists)', fn() => $clubs->add(['name' => 'Robotics Club']));

// 8.4 – delete a club that still has events
attempt('delete club #1 (has events)', fn() => $clubs->delete(1));

// 8.5 – add a valid club and delete it again
$chess = attempt('add: club "Chess Club"', fn() => $clubs->add(['name' => 'Chess Club', 'faculty' => 'FENS', 'founded_year' => 2020]));
if ($chess) {
    attempt('delete club "Chess Club" (no events)', fn() => $clubs->delete($chess['id']));
}
