<?php
/**
 * PRESENTATION LAYER – clubs (finished example – read it before Task 3)
 *
 * Every route does only three things:
 *   1. read the request (path parameter @id, query parameters, JSON body)
 *   2. call ONE service method
 *   3. answer with JSON and the right status code (200 is the default, 201 for "created")
 *
 * Errors are NOT handled here: the service throws an exception (400, 404, ...) and the
 * error handler in index.php (Task 2.2) turns it into a JSON answer.
 *
 * @id:[0-9]+ means: this route matches only when the id is a number (/clubs/abc -> 404).
 */

Flight::route('GET /clubs', function () {
    Flight::json(Flight::club_service()->get_all());
});

Flight::route('GET /clubs/@id:[0-9]+', function ($id) {
    Flight::json(Flight::club_service()->get_by_id($id));
});

/** Body: { "name": "Chess Club", "faculty": "FENS", "founded_year": 2020 } */
Flight::route('POST /clubs', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::club_service()->add($data), 201);
});

Flight::route('PUT /clubs/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::club_service()->update($data, $id));
});

Flight::route('DELETE /clubs/@id:[0-9]+', function ($id) {
    Flight::club_service()->delete($id);
    Flight::json(['message' => 'Club deleted.']);
});
