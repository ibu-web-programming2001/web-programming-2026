<?php
/**
 * VERSION 1 – the "first try" (DO NOT DO THIS IN YOUR PROJECT)
 *
 * Problems we will fix step by step:
 *  1. Database credentials are hard-coded (and would end up on GitHub).
 *  2. Values are glued into the SQL string -> SQL INJECTION (see testv1.php).
 *  3. Every DAO would repeat the same connection code.
 */
class EventDaov1
{
    private $connection;

    public function __construct()
    {
        $host = '127.0.0.1';
        $db_name = 'campusevents';
        $username = 'root';
        $password = '';                     // <- a password in the source code!

        $this->connection = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function get_all()
    {
        $statement = $this->connection->query("SELECT * FROM events");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_by_id($id)
    {
        // DANGER: $id comes from the user and is glued into the query
        $statement = $this->connection->query("SELECT * FROM events WHERE id = " . $id);
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function search_by_title($title)
    {
        // DANGER: try $title = "' OR 1=1 -- "
        $statement = $this->connection->query("SELECT * FROM events WHERE title LIKE '%" . $title . "%'");
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
