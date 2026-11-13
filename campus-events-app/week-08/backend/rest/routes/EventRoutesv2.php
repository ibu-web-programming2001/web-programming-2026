<?php
/**
 * VERSION 2 – RESTful URLs and correct status codes, but a try/catch in EVERY route.
 * Registered under the prefix /v2 (see index.php), e.g. GET /v2/events/1
 *
 *   GET    /v2/events        200
 *   GET    /v2/events/{id}   200 / 404
 *   POST   /v2/events        201 / 400
 *   DELETE /v2/events/{id}   200 / 400 / 404
 *
 * Still a problem: the same try/catch is repeated in every route of every entity (and easy to forget).
 */

Flight::route('GET /events', function () {
    Flight::json(Flight::event_service()->get_all());
});

Flight::route('GET /events/@id', function ($id) {
    try {
        Flight::json(Flight::event_service()->get_by_id($id));
    } catch (HttpException $e) {
        Flight::json(['error' => $e->getMessage()], $e->get_status());
    }
});

Flight::route('POST /events', function () {
    try {
        $data = Flight::request()->data->getData();
        Flight::json(Flight::event_service()->add($data), 201);
    } catch (HttpException $e) {
        Flight::json(['error' => $e->getMessage()], $e->get_status());
    }
});

Flight::route('DELETE /events/@id', function ($id) {
    try {
        Flight::event_service()->delete($id);
        Flight::json(['message' => 'Event deleted.']);
    } catch (HttpException $e) {
        Flight::json(['error' => $e->getMessage()], $e->get_status());
    }
});
