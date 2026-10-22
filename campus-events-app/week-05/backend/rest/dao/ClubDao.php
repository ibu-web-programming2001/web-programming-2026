<?php
require_once __DIR__ . '/BaseDao.php';

class ClubDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('clubs', ['name', 'faculty', 'founded_year', 'description']);
    }

    /** All clubs with the number of their events. */
    public function get_all()
    {
        return $this->query(
            "SELECT c.*, (SELECT COUNT(*) FROM events e WHERE e.club_id = c.id) AS event_count
             FROM clubs c
             ORDER BY c.name"
        );
    }

    public function get_by_name($name)
    {
        return $this->query_unique("SELECT * FROM clubs WHERE name = :name", ['name' => $name]);
    }
}
