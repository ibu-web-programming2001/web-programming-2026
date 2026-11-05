<?php
require_once __DIR__ . '/BaseDao.php';

class CategoryDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('categories', ['name', 'description']);
    }

    /** All categories with the number of events in each one. */
    public function get_all()
    {
        return $this->query(
            "SELECT c.*, (SELECT COUNT(*) FROM events e WHERE e.category_id = c.id) AS event_count
             FROM categories c
             ORDER BY c.name"
        );
    }

    public function get_by_name($name)
    {
        return $this->query_unique("SELECT * FROM categories WHERE name = :name", ['name' => $name]);
    }
}
