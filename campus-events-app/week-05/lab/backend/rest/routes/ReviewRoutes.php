<?php
/**
 * PRESENTATION LAYER – reviews (finished)
 * The reviews of ONE event are a nested resource: GET /events/@id/reviews (Task 4.3).
 */

Flight::route('GET /reviews', function () {
    Flight::json(Flight::review_service()->get_all());
});

Flight::route('GET /reviews/@id:[0-9]+', function ($id) {
    Flight::json(Flight::review_service()->get_by_id($id));
});

/** Body: { "user_id": 3, "event_id": 1, "rating": 5, "comment": "Great!" } */
Flight::route('POST /reviews', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::review_service()->add($data), 201);
});

Flight::route('PUT /reviews/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::review_service()->update($data, $id));
});

Flight::route('DELETE /reviews/@id:[0-9]+', function ($id) {
    Flight::review_service()->delete($id);
    Flight::json(['message' => 'Review deleted.']);
});
