<?php
/**
 * Authorization VERSION 1 – the role is checked by hand inside the route (DO NOT DO THIS IN YOUR PROJECT)
 *
 *   DELETE /v1/categories/{id}      (admins only)
 *
 * Works, but the same if-block has to be copied into every admin route – and changing the rule
 * ("club leaders may delete too") means editing all of them.
 * VERSION 2 (final): ->addMiddleware(new RoleMiddleware([Roles::ADMIN])) – see CategoryRoutes.php.
 */
Flight::route('DELETE /v1/categories/@id:[0-9]+', function ($id) {
    $user = Flight::get('user');
    if ($user['role'] !== 'admin') {
        Flight::json(['error' => 'You do not have permission to do this.'], 403);
        return;
    }

    Flight::category_service()->delete($id);
    Flight::json(['message' => 'Category deleted.']);
});
