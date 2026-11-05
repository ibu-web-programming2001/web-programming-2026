<?php
require_once __DIR__ . '/BaseDao.php';

class UserDao extends BaseDao
{
    /** Never SELECT the password hash unless we really need it (login). */
    private const PUBLIC_COLUMNS = "id, first_name, last_name, email, role, faculty, two_factor_enabled, created_at";

    public function __construct()
    {
        parent::__construct('users', ['first_name', 'last_name', 'email', 'password', 'role', 'faculty', 'two_factor_secret', 'two_factor_enabled']);
    }

    public function get_all()
    {
        return $this->query("SELECT " . self::PUBLIC_COLUMNS . " FROM users ORDER BY first_name, last_name");
    }

    public function get_by_id($id)
    {
        return $this->query_unique("SELECT " . self::PUBLIC_COLUMNS . " FROM users WHERE id = :id", ['id' => $id]);
    }

    /** Includes the password hash and the 2FA secret – used only by AuthService. */
    public function get_by_email($email)
    {
        return $this->query_unique("SELECT * FROM users WHERE email = :email", ['email' => $email]);
    }

    /** Full row including secrets – only for AuthService (2FA checks). */
    public function get_with_secrets($id)
    {
        return $this->query_unique("SELECT * FROM users WHERE id = :id", ['id' => $id]);
    }

    public function count_by_role($role)
    {
        return (int) $this->query_unique("SELECT COUNT(*) AS total FROM users WHERE role = :role", ['role' => $role])['total'];
    }
}
