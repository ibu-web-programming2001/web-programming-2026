<?php
/**
 * PRESENTATION LAYER – categories
 * ClubRoutes.php is a finished example of exactly the same five routes.
 */

/*
 * TODO Task 3.1 – the five REST routes for categories. Every route calls Flight::category_service():
 *
 *   GET    /categories                 -> get_all()                                   200
 *   GET    /categories/@id:[0-9]+      -> get_by_id($id)                              200 / 404
 *   POST   /categories                 -> add($data)                                  201 / 400
 *   PUT    /categories/@id:[0-9]+      -> update($data, $id)                          200 / 400 / 404
 *   DELETE /categories/@id:[0-9]+      -> delete($id), then answer
 *                                         Flight::json(['message' => 'Category deleted.']);   200 / 400 / 404
 *
 *   The JSON body of POST and PUT:  $data = Flight::request()->data->getData();
 *   201 for POST:                   Flight::json($result, 201);
 *   No try/catch – the error handler from Task 2.2 answers 400/404 for you.
 *
 * TODO Task 3.2 – NEW: the events of one category (a nested resource):
 *   GET    /categories/@id:[0-9]+/events -> Flight::event_service()->get_by_category($id)  200 / 404
 *   (EventService::get_by_category() from Lab 4 answers 404 "Category not found.")
 */

Flight::route('GET /categories', function () {
    Flight::json(Flight::category_service()->get_all());
});

Flight::route('GET /categories/@id:[0-9]+', function ($id) {
    Flight::json(Flight::category_service()->get_by_id($id));
});

Flight::route('POST /categories', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::category_service()->add($data), 201);
});

Flight::route('PUT /categories/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::category_service()->update($data, $id));
});

Flight::route('DELETE /categories/@id:[0-9]+', function ($id) {
    Flight::category_service()->delete($id);
    Flight::json(['message' => 'Category deleted.']);
});

Flight::route('GET /categories/@id:[0-9]+/events', function ($id) {
    Flight::json(Flight::event_service()->get_by_category($id));
});
