<?php
/**
 * VERSION 3 (FINAL) – PRESENTATION LAYER – categories
 * Read the request, call the service, answer with JSON + the right status code.
 * Errors are NOT handled here: services throw exceptions and the global
 * error handler in index.php turns them into JSON (400, 404, 500...).
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
