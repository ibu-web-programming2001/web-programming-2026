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
}
