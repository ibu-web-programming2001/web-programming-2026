<?php
/**
 * VERSION 3 (FINAL) – PRESENTATION LAYER – events
 */

Flight::route('GET /events', function () {
    Flight::json(Flight::event_service()->get_all());
});

Flight::route('GET /events/@id:[0-9]+', function ($id) {
    Flight::json(Flight::event_service()->get_by_id($id));
});

Flight::route('GET /events/@id:[0-9]+/reviews', function ($id) {
    Flight::json(Flight::review_service()->get_by_event($id));
});

Flight::route('GET /events/@id:[0-9]+/weather', function ($id) {
    Flight::json(Flight::event_service()->get_weather($id));
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
