<?php
/**
 * VERSION 3 (FINAL) – PRESENTATION LAYER – registrations
 * GET /registrations?user_id=2 returns only the registrations of one student (query parameter).
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

/** Register for an event: { "user_id": 3, "event_id": 12 } */
Flight::route('POST /registrations', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::registration_service()->add($data), 201);
});

/** Change the status: { "status": "attended" } or { "status": "cancelled" } */
Flight::route('PUT /registrations/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::registration_service()->update($data, $id));
});

/** Cancel a registration – an action on a resource, so it gets its own sub-path */
Flight::route('PUT /registrations/@id:[0-9]+/cancel', function ($id) {
    Flight::json(Flight::registration_service()->cancel($id));
});

/** Check a student in at the event */
Flight::route('PUT /registrations/@id:[0-9]+/attend', function ($id) {
    Flight::json(Flight::registration_service()->mark_attended($id));
});

Flight::route('DELETE /registrations/@id:[0-9]+', function ($id) {
    Flight::registration_service()->delete($id);
    Flight::json(['message' => 'Registration deleted.']);
});
