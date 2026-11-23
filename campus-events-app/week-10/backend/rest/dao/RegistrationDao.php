<?php
require_once __DIR__ . '/BaseDao.php';

class RegistrationDao extends BaseDao
{
    /** Registration columns + event title/date/place + student name + calculated "is_upcoming" flag. */
    private const SELECT = "
        SELECT r.*,
               e.title     AS event_title,
               e.starts_at AS event_starts_at,
               e.location  AS event_location,
               CONCAT(u.first_name, ' ', u.last_name) AS user_name,
               (r.status = 'registered' AND e.starts_at > NOW()) AS is_upcoming
        FROM registrations r
        JOIN events e ON e.id = r.event_id
        JOIN users u  ON u.id = r.user_id";

    public function __construct()
    {
        parent::__construct('registrations', ['user_id', 'event_id', 'registered_at', 'cancelled_at', 'status']);
    }

    public function get_all()
    {
        return $this->query(self::SELECT . " ORDER BY e.starts_at DESC, r.id DESC");
    }

    public function get_by_id($id)
    {
        return $this->query_unique(self::SELECT . " WHERE r.id = :id", ['id' => $id]);
    }

    public function get_by_user($user_id)
    {
        return $this->query(self::SELECT . " WHERE r.user_id = :user_id ORDER BY e.starts_at DESC, r.id DESC", ['user_id' => $user_id]);
    }

    /** Registrations of a student for events that have not started yet (the "max. 3" rule). */
    public function count_upcoming_by_user($user_id)
    {
        return (int) $this->query_unique(
            "SELECT COUNT(*) AS total
             FROM registrations r JOIN events e ON e.id = r.event_id
             WHERE r.user_id = :user_id AND r.status = 'registered' AND e.starts_at > NOW()",
            ['user_id' => $user_id]
        )['total'];
    }

    /** Seats that are taken: registered + attended (cancelled registrations gave their seat back). */
    public function count_taken_by_event($event_id)
    {
        return (int) $this->query_unique(
            "SELECT COUNT(*) AS total FROM registrations WHERE event_id = :event_id AND status <> 'cancelled'",
            ['event_id' => $event_id]
        )['total'];
    }

    public function get_active_by_user_and_event($user_id, $event_id)
    {
        return $this->query_unique(
            "SELECT * FROM registrations WHERE user_id = :user_id AND event_id = :event_id AND status <> 'cancelled'",
            ['user_id' => $user_id, 'event_id' => $event_id]
        );
    }

    /* ---------- statistics for the dashboard ---------- */

    public function count_upcoming()
    {
        return (int) $this->query_unique(
            "SELECT COUNT(*) AS total
             FROM registrations r JOIN events e ON e.id = r.event_id
             WHERE r.status = 'registered' AND e.starts_at > NOW()"
        )['total'];
    }

    public function count_attended()
    {
        return (int) $this->query_unique("SELECT COUNT(*) AS total FROM registrations WHERE status = 'attended'")['total'];
    }

    /** Registrations for events that start within $days days (optionally only for one student). */
    public function get_starting_soon($days, $user_id = null)
    {
        $query = self::SELECT . " WHERE r.status = 'registered' AND e.starts_at > NOW()
                                  AND e.starts_at <= NOW() + INTERVAL :days DAY";
        $params = ['days' => (int) $days];
        if ($user_id !== null) {
            $query .= " AND r.user_id = :user_id";
            $params['user_id'] = $user_id;
        }
        return $this->query($query . " ORDER BY e.starts_at", $params);
    }

    /** Taken seats per category – LEFT JOIN keeps categories without registrations (total 0). */
    public function count_per_category()
    {
        return $this->query(
            "SELECT c.name AS category, COUNT(r.id) AS total
             FROM categories c
             LEFT JOIN events e        ON e.category_id = c.id
             LEFT JOIN registrations r ON r.event_id = e.id AND r.status <> 'cancelled'
             GROUP BY c.id, c.name
             ORDER BY c.id"
        );
    }
}
