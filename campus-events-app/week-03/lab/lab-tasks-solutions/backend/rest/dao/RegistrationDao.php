<?php
require_once __DIR__ . '/BaseDao.php';
require_once __DIR__ . '/EventDao.php';

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

    /* ---------- Lab 03 – Task 5 ---------- */

    /** Taken seats per event. taken + available_seats must always equal capacity. */
    public function count_per_event()
    {
        return $this->query(
            "SELECT e.id, e.title, e.capacity, e.available_seats, COUNT(r.id) AS taken
             FROM events e
             LEFT JOIN registrations r ON r.event_id = e.id AND r.status <> 'cancelled'
             GROUP BY e.id, e.title, e.capacity, e.available_seats
             ORDER BY taken DESC, e.title"
        );
    }

    /* ---------- Lab 03 – Task 7: transactions ---------- */

    /** Take a seat AND insert the registration – both happen, or neither does. */
    public function register($user_id, $event_id)
    {
        if ($this->get_active_by_user_and_event($user_id, $event_id) !== null) {
            throw new Exception("User $user_id is already registered for event $event_id");
        }

        $events = new EventDao();
        $this->begin_transaction();
        try {
            if ($events->change_available_seats($event_id, -1) === 0) {
                throw new Exception("Event $event_id has no free seats");
            }
            $registration = $this->add([
                'user_id' => $user_id,
                'event_id' => $event_id,
                'registered_at' => date('Y-m-d H:i:s'),
                'status' => 'registered'
            ]);
            $this->commit();
            return $registration;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /** Mark the registration as cancelled AND give the seat back – both or neither. */
    public function cancel($registration_id)
    {
        $registration = $this->get_by_id($registration_id);
        if ($registration === null || $registration['status'] !== 'registered') {
            throw new Exception("Registration $registration_id cannot be cancelled");
        }

        $events = new EventDao();
        $this->begin_transaction();
        try {
            $this->update(['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s')], $registration_id);
            $events->change_available_seats($registration['event_id'], +1);
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
