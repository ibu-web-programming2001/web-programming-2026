<?php
/**
 * PRESENTATION LAYER – registrations
 *
 * Authorization:
 *  - admins: everything (register anybody, check in, delete)
 *  - students: see/cancel ONLY their own registrations, register only themselves
 *  The ownership checks are business rules -> they live in RegistrationService (check_owner).
 */

Flight::route('GET /registrations', function () {
    $user_id = Flight::request()->query['user_id'] ?? null;
    Flight::json(Flight::registration_service()->get_for_user(Flight::get('user'), $user_id));
});

Flight::route('GET /registrations/@id:[0-9]+', function ($id) {
    Flight::json(Flight::registration_service()->get_by_id_for($id, Flight::get('user')));
});

Flight::route('POST /registrations', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::registration_service()->add($data, Flight::get('user')), 201);
});

Flight::route('PUT /registrations/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::registration_service()->update($data, $id, Flight::get('user')));
})->addMiddleware(new RoleMiddleware([Roles::ADMIN]));

Flight::route('PUT /registrations/@id:[0-9]+/cancel', function ($id) {
    Flight::json(Flight::registration_service()->cancel($id, Flight::get('user')));
});

Flight::route('PUT /registrations/@id:[0-9]+/attend', function ($id) {
    Flight::json(Flight::registration_service()->mark_attended($id));
})->addMiddleware(new RoleMiddleware([Roles::ADMIN]));

Flight::route('DELETE /registrations/@id:[0-9]+', function ($id) {
    Flight::registration_service()->delete($id);
    Flight::json(['message' => 'Registration deleted.']);
})->addMiddleware(new RoleMiddleware([Roles::ADMIN]));
