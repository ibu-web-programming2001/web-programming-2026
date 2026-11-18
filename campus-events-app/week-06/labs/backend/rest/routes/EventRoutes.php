<?php
/**
 * PRESENTATION LAYER – events  (Lab 5 – finished)
 * The OpenAPI (Swagger) documentation of these routes is in public/v1/docs/paths/EventPaths.php.
 */

Flight::route('GET /events', function () {
    $query = Flight::request()->query;

    if (isset($query['search'])) {
        Flight::json(Flight::event_service()->search($query['search']));
        return;
    }

    $filters = array_intersect_key($query->getData(), array_flip(['club_id', 'category_id', 'city', 'only_upcoming', 'min_free_seats']));
    if (count($filters) > 0) {
        Flight::json(Flight::event_service()->filter($filters));
    } else {
        Flight::json(Flight::event_service()->get_all());
    }
});

Flight::route('GET /events/@id:[0-9]+', function ($id) {
    Flight::json(Flight::event_service()->get_by_id($id));
});

/** Body: { "title": "...", "club_id": 2, "category_id": 2, "location": "...", "starts_at": "2026-12-01 18:00:00", "capacity": 30 } */
Flight::route('POST /events', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::event_service()->add($data), 201);
});

Flight::route('PUT /events/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::event_service()->update($data, $id));
});

Flight::route('DELETE /events/@id:[0-9]+', function ($id) {
    Flight::event_service()->delete($id);
    Flight::json(['message' => 'Event deleted.']);
});

Flight::route('GET /events/@id:[0-9]+/reviews', function ($id) {
    Flight::json(Flight::review_service()->get_by_event($id));
});

Flight::route('GET /clubs/@id:[0-9]+/events', function ($id) {
    Flight::json(Flight::event_service()->get_by_club($id));
});
