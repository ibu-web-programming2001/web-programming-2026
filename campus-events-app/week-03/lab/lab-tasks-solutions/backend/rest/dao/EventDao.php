<?php
require_once __DIR__ . '/BaseDao.php';

class EventDao extends BaseDao
{
    /** Event columns + club/category names + rating summary + "is it still to come?" (JOINs instead of extra requests). */
    private const SELECT = "
        SELECT e.*,
               cl.name AS club_name,
               c.name  AS category_name,
               (e.starts_at > NOW()) AS is_upcoming,
               (SELECT ROUND(AVG(r.rating), 1) FROM reviews r WHERE r.event_id = e.id) AS average_rating,
               (SELECT COUNT(*) FROM reviews r WHERE r.event_id = e.id) AS review_count
        FROM events e
        JOIN clubs cl     ON cl.id = e.club_id
        JOIN categories c ON c.id = e.category_id";

    public function __construct()
    {
        parent::__construct('events', [
            'title', 'club_id', 'category_id', 'location', 'city',
            'starts_at', 'capacity', 'available_seats', 'description'
        ]);
    }

    public function get_all()
    {
        return $this->query(self::SELECT . " ORDER BY e.starts_at");
    }

    public function get_by_id($id)
    {
        return $this->query_unique(self::SELECT . " WHERE e.id = :id", ['id' => $id]);
    }

    /** Used to stop the same event from being entered twice. */
    public function get_by_title_and_start($title, $starts_at)
    {
        return $this->query_unique(
            "SELECT * FROM events WHERE title = :title AND starts_at = :starts_at",
            ['title' => $title, 'starts_at' => $starts_at]
        );
    }

    /** Upcoming events that start within $days days (for "Starting soon" on the dashboard). */
    public function get_starting_soon($days)
    {
        return $this->query(
            self::SELECT . " WHERE e.starts_at > NOW() AND e.starts_at <= NOW() + INTERVAL :days DAY ORDER BY e.starts_at",
            ['days' => (int) $days]
        );
    }

    public function count_upcoming()
    {
        return (int) $this->query_unique("SELECT COUNT(*) AS total FROM events WHERE starts_at > NOW()")['total'];
    }

    public function count_by_club($club_id)
    {
        return (int) $this->query_unique("SELECT COUNT(*) AS total FROM events WHERE club_id = :id", ['id' => $club_id])['total'];
    }

    public function count_by_category($category_id)
    {
        return (int) $this->query_unique("SELECT COUNT(*) AS total FROM events WHERE category_id = :id", ['id' => $category_id])['total'];
    }

    /**
     * Change the number of free seats by +1 / -1 in ONE atomic SQL statement.
     * Returns 0 if the result would be below 0 or above the capacity.
     */
    public function change_available_seats($id, $delta)
    {
        return $this->execute(
            "UPDATE events
             SET available_seats = available_seats + :delta
             WHERE id = :id AND available_seats + :delta2 BETWEEN 0 AND capacity",
            ['delta' => $delta, 'delta2' => $delta, 'id' => $id]
        );
    }

    /* ---------- Lab 03 – Task 3: finders ---------- */

    /** All events of one club, the soonest first. */
    public function get_by_club($club_id)
    {
        return $this->query(self::SELECT . " WHERE e.club_id = :club_id ORDER BY e.starts_at", ['club_id' => $club_id]);
    }

    /** All events of one category, the soonest first. */
    public function get_by_category($category_id)
    {
        return $this->query(self::SELECT . " WHERE e.category_id = :category_id ORDER BY e.starts_at", ['category_id' => $category_id]);
    }

    /** Events whose title OR description contains $text (the % signs go into the VALUE, not into the SQL). */
    public function search($text)
    {
        return $this->query(
            self::SELECT . " WHERE e.title LIKE :title OR e.description LIKE :description ORDER BY e.starts_at",
            ['title' => '%' . $text . '%', 'description' => '%' . $text . '%']
        );
    }

    /* ---------- Lab 03 – Task 4: one method, many optional filters ---------- */

    /**
     * $filters can contain any of: club_id, category_id, city, only_upcoming, min_free_seats.
     * Only the filters that are given end up in the WHERE part.
     */
    public function filter($filters = [])
    {
        $where = [];
        $params = [];

        if (!empty($filters['club_id'])) {
            $where[] = "e.club_id = :club_id";
            $params['club_id'] = $filters['club_id'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = "e.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }
        if (!empty($filters['city'])) {
            $where[] = "e.city = :city";
            $params['city'] = $filters['city'];
        }
        if (!empty($filters['only_upcoming'])) {
            $where[] = "e.starts_at > NOW()";
        }
        if (isset($filters['min_free_seats'])) {
            $where[] = "e.available_seats >= :min_free_seats";
            $params['min_free_seats'] = (int) $filters['min_free_seats'];
        }

        $query = self::SELECT;
        if (count($where) > 0) {
            $query .= " WHERE " . implode(' AND ', $where);
        }
        return $this->query($query . " ORDER BY e.starts_at", $params);
    }
}
