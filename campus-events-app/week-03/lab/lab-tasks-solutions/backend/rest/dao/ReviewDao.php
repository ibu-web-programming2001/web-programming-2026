<?php
require_once __DIR__ . '/BaseDao.php';

class ReviewDao extends BaseDao
{
    private const SELECT = "
        SELECT r.*,
               CONCAT(u.first_name, ' ', u.last_name) AS user_name,
               e.title AS event_title
        FROM reviews r
        JOIN users u  ON u.id = r.user_id
        JOIN events e ON e.id = r.event_id";

    public function __construct()
    {
        parent::__construct('reviews', ['user_id', 'event_id', 'rating', 'comment']);
    }

    public function get_all()
    {
        return $this->query(self::SELECT . " ORDER BY r.created_at DESC, r.id DESC");
    }

    public function get_by_id($id)
    {
        return $this->query_unique(self::SELECT . " WHERE r.id = :id", ['id' => $id]);
    }

    public function get_by_event($event_id)
    {
        return $this->query(self::SELECT . " WHERE r.event_id = :event_id ORDER BY r.created_at DESC, r.id DESC", ['event_id' => $event_id]);
    }

    public function get_by_user_and_event($user_id, $event_id)
    {
        return $this->query_unique(
            "SELECT * FROM reviews WHERE user_id = :user_id AND event_id = :event_id",
            ['user_id' => $user_id, 'event_id' => $event_id]
        );
    }

    public function count_by_user($user_id)
    {
        return (int) $this->query_unique("SELECT COUNT(*) AS total FROM reviews WHERE user_id = :id", ['id' => $user_id])['total'];
    }

    /* ---------- Lab 03 – Task 5 ---------- */

    /** Average rating per club – LEFT JOINs keep clubs without reviews (average NULL, count 0). */
    public function average_per_club()
    {
        return $this->query(
            "SELECT cl.name AS club, COUNT(rv.id) AS review_count, ROUND(AVG(rv.rating), 1) AS average_rating
             FROM clubs cl
             LEFT JOIN events e   ON e.club_id = cl.id
             LEFT JOIN reviews rv ON rv.event_id = e.id
             GROUP BY cl.id, cl.name
             ORDER BY average_rating DESC, cl.name"
        );
    }
}
