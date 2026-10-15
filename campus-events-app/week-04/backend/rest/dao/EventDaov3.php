<?php
require_once __DIR__ . '/BaseDaov1.php';

/**
 * VERSION 3 – EventDao extends BaseDaov1
 * get_all() and get_by_id() are inherited. Only event-specific methods are written here.
 */
class EventDaov3 extends BaseDaov1
{
    public function __construct()
    {
        parent::__construct('events');
    }

    public function search_by_title($title)
    {
        return $this->query("SELECT * FROM events WHERE title LIKE :title", ['title' => '%' . $title . '%']);
    }

    // INSERT/UPDATE/DELETE still written by hand – every DAO would need its own copy...
    public function add($event)
    {
        $statement = $this->connection->prepare(
            "INSERT INTO events (title, club_id, category_id, location, starts_at, capacity, available_seats)
             VALUES (:title, :club_id, :category_id, :location, :starts_at, :capacity, :capacity2)"
        );
        $statement->execute([
            'title' => $event['title'],
            'club_id' => $event['club_id'],
            'category_id' => $event['category_id'],
            'location' => $event['location'],
            'starts_at' => $event['starts_at'],
            'capacity' => $event['capacity'],
            'capacity2' => $event['capacity']
        ]);
        $event['id'] = $this->connection->lastInsertId();
        return $event;
    }

    public function delete($id)
    {
        $statement = $this->connection->prepare("DELETE FROM events WHERE id = :id");
        $statement->execute(['id' => $id]);
    }
}
