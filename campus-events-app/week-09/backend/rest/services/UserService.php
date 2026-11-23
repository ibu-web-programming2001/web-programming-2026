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

    public function add($user)
    {
        $user = $this->strip_secrets($user);
        $user['role'] = $user['role'] ?? Roles::STUDENT;
        $user = $this->empty_to_null($user, ['faculty']);
        $this->validate($user);
        Validator::required($user, ['password']);
        Validator::password($user['password']);

        // Rule: passwords are NEVER stored in plain text
        $user['password'] = password_hash($user['password'], PASSWORD_BCRYPT);
        $user['email'] = strtolower(trim($user['email']));

        $created = $this->dao->add($user);
        return $this->dao->get_by_id($created['id']);   // get_by_id does not return the hash
    }

    public function update($user, $id)
    {
        $this->get_by_id($id);
        $user = $this->strip_secrets($user);
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

    /** Rule: an admin cannot delete their own account (the app would lose its last admin). */
    public function delete($id, $current_user = null)
    {
        $this->get_by_id($id);
        if ($current_user !== null && (int) $current_user['id'] === (int) $id) {
            throw new ValidationException('You cannot delete your own account.');
        }
        $this->dao->delete($id);
    }

    /** 2FA fields may only be changed by AuthService – never from a request body. */
    private function strip_secrets($user)
    {
        unset($user['two_factor_secret'], $user['two_factor_enabled']);
        return $user;
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
