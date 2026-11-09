<?php
/**
 * PRESENTATION LAYER – registrations (finished)
 *
 *  - GET /registrations?user_id=2  a QUERY parameter filters the list
 *  - PUT /registrations/5/cancel   an ACTION on one resource gets its own sub-path
 */

Flight::route('GET /registrations', function () {
    $user_id = Flight::request()->query['user_id'] ?? null;
    if ($user_id) {
        Flight::json(Flight::registration_service()->get_by_user($user_id));
    } else {
        Flight::json(Flight::registration_service()->get_all());
    }
});

Flight::route('GET /registrations/@id:[0-9]+', function ($id) {
    Flight::json(Flight::registration_service()->get_by_id($id));
});

/** Register a student for an event. Body: { "user_id": 3, "event_id": 12 } */
Flight::route('POST /registrations', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::registration_service()->add($data), 201);
});

Flight::route('PUT /registrations/@id:[0-9]+/cancel', function ($id) {
    Flight::json(Flight::registration_service()->cancel($id));
});

Flight::route('PUT /registrations/@id:[0-9]+/attend', function ($id) {
    Flight::json(Flight::registration_service()->mark_attended($id));
});

/** Deleting an active registration gives the seat back (RegistrationService::delete()). */
Flight::route('DELETE /registrations/@id:[0-9]+', function ($id) {
    Flight::registration_service()->delete($id);
    Flight::json(['message' => 'Registration deleted.']);
});
