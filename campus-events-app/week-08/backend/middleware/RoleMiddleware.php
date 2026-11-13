<?php
require_once __DIR__ . '/../utils/exceptions.php';

/**
 * AUTHORIZATION middleware – "are you allowed to do this?"
 *
 * Reusable for any route and any roles:
 *
 *   Flight::route('DELETE /events/@id', ...)->addMiddleware(new RoleMiddleware([Roles::ADMIN]));
 *
 * Runs AFTER AuthMiddleware (group middleware runs first), so Flight::get('user') is already set.
 * Wrong role -> 403 Forbidden (the user IS logged in, but not allowed).
 */
class RoleMiddleware
{
    private $allowed_roles;

    public function __construct($allowed_roles)
    {
        $this->allowed_roles = $allowed_roles;
    }

    public function before($params)
    {
        $user = Flight::get('user');
        if (!in_array($user['role'] ?? null, $this->allowed_roles, true)) {
            throw new ForbiddenException('You do not have permission to do this.');
        }
    }
}
