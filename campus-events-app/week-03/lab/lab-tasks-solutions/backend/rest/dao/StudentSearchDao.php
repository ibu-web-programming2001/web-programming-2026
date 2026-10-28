<?php
require_once __DIR__ . '/BaseDao.php';

/**
 * Lab 03 – Task 2 (SOLUTION): the same four searches, now with prepared statements.
 * The user's text is sent as a VALUE (:email, :first ...), never glued into the SQL.
 */
class StudentSearchDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('users');
    }

    public function find_by_email($email)
    {
        return $this->query_unique(
            "SELECT id, first_name, last_name, email, role FROM users WHERE email = :email",
            ['email' => $email]
        );
    }

    public function search_by_name($text)
    {
        return $this->query(
            "SELECT id, first_name, last_name, email FROM users
             WHERE first_name LIKE :first OR last_name LIKE :last
             ORDER BY first_name",
            ['first' => '%' . $text . '%', 'last' => '%' . $text . '%']
        );
    }

    public function get_by_faculty($faculty)
    {
        return $this->query(
            "SELECT id, first_name, last_name, faculty FROM users
             WHERE role = 'student' AND faculty = :faculty
             ORDER BY first_name",
            ['faculty' => $faculty]
        );
    }

    public function count_registrations($user_id)
    {
        return (int) $this->query_unique(
            "SELECT COUNT(*) AS total FROM registrations WHERE user_id = :user_id",
            ['user_id' => (int) $user_id]
        )['total'];
    }
}
