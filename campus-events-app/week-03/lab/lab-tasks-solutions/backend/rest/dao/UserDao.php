<?php
require_once __DIR__ . '/BaseDao.php';

class UserDao extends BaseDao
{
    /** Never SELECT the password hash unless we really need it (login). */
    private const PUBLIC_COLUMNS = "id, first_name, last_name, email, role, faculty, created_at";

    public function __construct()
    {
        parent::__construct('users', ['first_name', 'last_name', 'email', 'password', 'role', 'faculty']);
    }

    public function get_all()
    {
        return $this->query("SELECT " . self::PUBLIC_COLUMNS . " FROM users ORDER BY first_name, last_name");
    }

    public function get_by_id($id)
    {
        return $this->query_unique("SELECT " . self::PUBLIC_COLUMNS . " FROM users WHERE id = :id", ['id' => $id]);
    }

    /** Includes the password hash – used only to check a login. */
    public function get_by_email($email)
    {
        return $this->query_unique("SELECT * FROM users WHERE email = :email", ['email' => $email]);
    }

    public function count_by_role($role)
    {
        return (int) $this->query_unique("SELECT COUNT(*) AS total FROM users WHERE role = :role", ['role' => $role])['total'];
    }

    /* ---------- Lab 03 – Task 5 ---------- */

    /** Students with the most active (not cancelled) registrations. LIMIT cannot be a quoted value, so we cast it to int. */
    public function get_most_active($limit = 3)
    {
        return $this->query(
            "SELECT u.id, u.first_name, u.last_name, COUNT(r.id) AS registrations
             FROM users u
             LEFT JOIN registrations r ON r.user_id = u.id AND r.status <> 'cancelled'
             WHERE u.role = 'student'
             GROUP BY u.id, u.first_name, u.last_name
             ORDER BY registrations DESC, u.first_name
             LIMIT " . (int) $limit
        );
    }
}
