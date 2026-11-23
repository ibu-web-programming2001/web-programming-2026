<?php
require_once __DIR__ . '/../../config/config.php';

/**
 * VERSION 3 – first BaseDao
 *
 * The connection and the READ methods are written ONCE and inherited by every DAO.
 *
 * Still a problem:
 *  - every child DAO still writes its own INSERT/UPDATE/DELETE (see EventDaov3)
 *  - every DAO object opens its OWN database connection
 */
class BaseDaov1
{
    protected $connection;
    protected $table_name;

    public function __construct($table_name)
    {
        $this->table_name = $table_name;
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

    protected function query($query, $params = [])
    {
        $statement = $this->connection->prepare($query);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    protected function query_unique($query, $params = [])
    {
        $results = $this->query($query, $params);
        return $results[0] ?? null;
    }

    public function get_all()
    {
        return $this->query("SELECT * FROM " . $this->table_name);
    }

    public function get_by_id($id)
    {
        return $this->query_unique("SELECT * FROM " . $this->table_name . " WHERE id = :id", ['id' => $id]);
    }
}
