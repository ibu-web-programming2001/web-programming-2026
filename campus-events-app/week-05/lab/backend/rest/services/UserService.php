<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../../utils/Roles.php';

class UserService extends BaseService
{
    const FACULTIES = ['FENS', 'FASS', 'FBA', 'FEDU'];

    protected $entity_name = 'User';

    public function __construct($dao = null)
    {
        parent::__construct($dao ?? new UserDao());
    }

    public function get_by_email($email)
    {
        return $this->dao->get_by_email($email);
    }

    /*
     * TODO Task 7 – add($user)
     *   1. A new user is a student unless a role is given:   $user['role'] = $user['role'] ?? Roles::STUDENT;
     *   2. $user = $this->empty_to_null($user, ['faculty']);
     *   3. $this->validate($user);                          – written for you (names, email, role, faculty, unique email)
     *   4. The password is required and strong:
     *        Validator::required($user, ['password']);   Validator::password($user['password']);
     *   5. Rule: passwords are NEVER stored in plain text:
     *        $user['password'] = password_hash($user['password'], PASSWORD_BCRYPT);
     *   6. Emails are stored in lowercase:                    $user['email'] = strtolower(trim($user['email']));
     *   7. $created = $this->dao->add($user);  and return $this->dao->get_by_id($created['id']);
     *      (UserDao::get_by_id() does not select the password hash, so the hash never leaves the service.)
     */
    public function add($user)
    {
        $user['role'] = $user['role'] ?? Roles::STUDENT;
        $user = $this->empty_to_null($user, ['faculty']);
        $this->validate($user);

        // check the password BEFORE hashing it – a hash is always 60 characters long
        Validator::required($user, ['password']);
        Validator::password($user['password']);
        $user['password'] = password_hash($user['password'], PASSWORD_BCRYPT);
        $user['email'] = strtolower(trim($user['email']));

        $created = $this->dao->add($user);
        return $this->dao->get_by_id($created['id']);
    }

    public function update($user, $id)
    {
        $this->get_by_id($id);
        $user = $this->empty_to_null($user, ['faculty']);
        $this->validate($user, $id);
        $user['email'] = strtolower(trim($user['email']));

        // An empty password means "keep the current one"
        if (empty($user['password'])) {
            unset($user['password']);
        } else {
            Validator::password($user['password']);
            $user['password'] = password_hash($user['password'], PASSWORD_BCRYPT);
        }

        $this->dao->update($user, $id);
        return $this->dao->get_by_id($id);
    }

    protected function validate($user, $id = null)
    {
        Validator::required($user, ['first_name', 'last_name', 'email', 'role']);
        Validator::length($user['first_name'], 'first_name', 2, 50);
        Validator::length($user['last_name'], 'last_name', 2, 50);
        Validator::email($user['email']);
        Validator::one_of($user['role'], 'role', Roles::all());
        if (isset($user['faculty'])) {
            Validator::one_of($user['faculty'], 'faculty', self::FACULTIES);
        }

        // Rule: one account per email address
        $existing = $this->dao->get_by_email(strtolower(trim($user['email'])));
        if ($existing && $existing['id'] != $id) {
            throw new ValidationException("An account with this email already exists.");
        }
    }
}
