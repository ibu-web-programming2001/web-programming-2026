<?php
require_once __DIR__ . '/../../config/config.php';

/**
 * BaseDao: reusable CRUD for every table (Tutorial Steps 4–6).
 *  + INSERT/UPDATE/DELETE are generic – built from the array keys, written only once
 *  + only whitelisted columns can be written
 *  + ONE shared connection for all DAOs
 *  + transactions
 *
 * Child classes only say WHICH table they use and WHICH columns may be written:
 *
 *   class CategoryDao extends BaseDao {
 *       public function __construct() { parent::__construct('categories', ['name', 'description']); }
 *   }
 */
class BaseDao
{
    /** One PDO connection shared by all DAOs (created on first use). */
    private static $connection = null;

    protected $table_name;
    /** Columns that may be inserted/updated – everything else is ignored (protects against injected column names). */
    protected $columns;

    public function __construct($table_name, $columns = [])
    {
        $this->table_name = $table_name;
        $this->columns = $columns;
    }

    public static function connection()
    {
        if (self::$connection === null) {
            self::$connection = new PDO(
                "mysql:host=" . Config::DB_HOST() . ";port=" . Config::DB_PORT() . ";dbname=" . Config::DB_NAME() . ";charset=utf8mb4",
                Config::DB_USER(),
                Config::DB_PASSWORD(),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        }
        return self::$connection;
    }

    /* ---------- helpers for child classes ---------- */

    /** Run a SELECT and return all rows. */
    protected function query($query, $params = [])
    {
        $stmt = self::connection()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Run a SELECT and return the first row (or null). */
    protected function query_unique($query, $params = [])
    {
        $results = $this->query($query, $params);
        return $results[0] ?? null;
    }

    /** Run INSERT/UPDATE/DELETE and return the number of affected rows. */
    protected function execute($query, $params = [])
    {
        $stmt = self::connection()->prepare($query);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /* ---------- CRUD ---------- */

    public function get_all()
    {
        return $this->query("SELECT * FROM {$this->table_name} ORDER BY id");
    }

    public function get_by_id($id)
    {
        return $this->query_unique("SELECT * FROM {$this->table_name} WHERE id = :id", ['id' => $id]);
    }

    public function count_all()
    {
        return (int) $this->query_unique("SELECT COUNT(*) AS total FROM {$this->table_name}")['total'];
    }

    /**
     * INSERT INTO events (title, location) VALUES (:title, :location)
     */
    public function add($entity)
    {
        $entity = $this->only_columns($entity);
        $columns = array_keys($entity);

        $query = "INSERT INTO {$this->table_name} (" . implode(', ', $columns) . ")"
               . " VALUES (:" . implode(', :', $columns) . ")";
        $this->execute($query, $entity);

        $entity['id'] = (int) self::connection()->lastInsertId();
        return $entity;
    }

    /**
     * UPDATE events SET title = :title, location = :location WHERE id = :id
     */
    public function update($entity, $id)
    {
        $entity = $this->only_columns($entity);
        if (count($entity) === 0) {
            return 0;
        }

        $set = [];
        foreach (array_keys($entity) as $column) {
            $set[] = "$column = :$column";
        }
        $query = "UPDATE {$this->table_name} SET " . implode(', ', $set) . " WHERE id = :id";

        $entity['id'] = $id;
        return $this->execute($query, $entity);
    }

    public function delete($id)
    {
        return $this->execute("DELETE FROM {$this->table_name} WHERE id = :id", ['id' => $id]);
    }

    /* ---------- transactions (used when one action changes several tables) ---------- */

    public function begin_transaction() { self::connection()->beginTransaction(); }
    public function commit()            { self::connection()->commit(); }
    public function rollback()          { if (self::connection()->inTransaction()) self::connection()->rollBack(); }

    /** Keep only keys that are real, writable columns of this table. */
    protected function only_columns($entity)
    {
        return array_intersect_key($entity, array_flip($this->columns));
    }
}
