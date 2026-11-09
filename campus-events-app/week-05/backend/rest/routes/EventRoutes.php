<?php
/**
 * VERSION 3 (FINAL) – PRESENTATION LAYER – events
 */

Flight::route('GET /events', function () {
   // Flight::json(Flight::request());  // for debugging only, shows query parameters
    Flight::json(Flight::event_service()->get_all());
});

/** Path parameter: GET /events/5  – the regex [0-9]+ is checked by the router */
Flight::route('GET /events/@id:[0-9]+', function ($id) {
    Flight::json(Flight::event_service()->get_by_id($id));
});

/**
 * Query parameter: GET /event?id=5  – for comparison only.
 * The router ignores the query string, so we must read and validate id ourselves.
 */
Flight::route('GET /event', function () {
    $id = Flight::request()->query['id'];
    if ($id === null || !ctype_digit((string) $id)) {
        throw new ValidationException('Query parameter "id" must be a positive integer.');
    }
    Flight::json(Flight::event_service()->get_by_id($id));
});

/** Nested resource: all reviews of one event */
Flight::route('GET /events/@id:[0-9]+/reviews', function ($id) {
    Flight::json(Flight::review_service()->get_by_event($id));
});

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
