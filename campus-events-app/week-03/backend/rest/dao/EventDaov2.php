<?php
require_once __DIR__ . '/../../config/config.php';

/**
 * VERSION 2 – safe, but long
 *
 * Fixed:
 *  + credentials come from config.php (not committed to Git)
 *  + PREPARED STATEMENTS: the SQL and the values travel separately -> no SQL injection
 *  + full CRUD
 *
 * Still a problem:
 *  - every DAO (clubs, categories, users...) would copy the connection code and the CRUD methods
 */
class EventDaov2
{
    private $connection;

    public function __construct()
    {
        $this->connection = new PDO(
            "mysql:host=" . Config::DB_HOST() . ";port=" . Config::DB_PORT() . ";dbname=" . Config::DB_NAME() . ";charset=utf8mb4",
            Config::DB_USER(),
            Config::DB_PASSWORD(),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }

    public function get_all()
    {
        $statement = $this->connection->prepare("SELECT * FROM events ORDER BY starts_at");
        $statement->execute();
        return $statement->fetchAll();
    }

    public function get_by_id($id)
    {
        // Positional placeholder "?"
        $statement = $this->connection->prepare(
            "SELECT * FROM events WHERE id = ?");
        $statement->execute([$id]);
        return $statement->fetch();
    }

    public function search_by_title($title)
    {
        // Named placeholder ":title" – even "x' OR '1'='1" is now just a strange title
        $statement = $this->connection->prepare("SELECT * FROM 
        events WHERE title LIKE :title");
        $statement->execute(['title' => '%' . $title . '%']);
        return $statement->fetchAll();
    }

    public function add($title, $club_id, $category_id, $location, $starts_at, $capacity)
    {
        $statement = $this->connection->prepare(
            "INSERT INTO events (title, club_id, category_id, location, starts_at, capacity, available_seats)
             VALUES (:title, :club_id, :category_id, :location, :starts_at, :capacity, :available_seats)"
        );
        $statement->execute([
            'title' => $title,
            'club_id' => $club_id,
            'category_id' => $category_id,
            'location' => $location,
            'starts_at' => $starts_at,
            'capacity' => $capacity,
            'available_seats' => $capacity
        ]);
        return $this->connection->lastInsertId();
    }

    public function update($id, $title, $capacity)
    {
        $statement = $this->connection->prepare("UPDATE events SET title = :title, capacity = :capacity, available_seats = :seats WHERE id = :id");
        return $statement->execute(['id' => $id, 'title' => $title, 'capacity' => $capacity, 'seats' => $capacity]);
    }

    public function delete($id)
    {
        $statement = $this->connection->prepare("DELETE FROM events WHERE id = :id");
        return $statement->execute(['id' => $id]);
    }
}
