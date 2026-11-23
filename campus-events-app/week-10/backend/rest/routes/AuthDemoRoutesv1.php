<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Authentication VERSION 1 – checking the token by hand inside a route (DO NOT DO THIS IN YOUR PROJECT)
 *
 *   GET /v1/my-registrations      Authorization: Bearer <token>
 *
 * It works – but these ~15 lines would have to be COPIED into every protected route (40+ routes).
 * Forget them once and that endpoint is open to everybody.
 * VERSION 2 (final) = middleware/AuthMiddleware.php, registered once for the whole group in index.php.
 */
Flight::route('GET /v1/my-registrations', function () {
    $header = Flight::request()->getHeader('Authorization');
    if (!$header || !str_starts_with($header, 'Bearer ')) {
        Flight::json(['error' => 'Missing authentication token.'], 401);
        return;
    }
    try {
        $decoded = JWT::decode(substr($header, 7), new Key(Config::JWT_SECRET(), 'HS256'));
    } catch (Exception $e) {
        Flight::json(['error' => 'Invalid authentication token.'], 401);
        return;
    }

    // ... and only now the real work of this route
    Flight::json(Flight::registration_service()->get_by_user($decoded->user->id));
});
