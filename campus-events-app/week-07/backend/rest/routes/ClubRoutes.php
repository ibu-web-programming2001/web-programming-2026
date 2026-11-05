<?php
/**
 * VERSION 3 (FINAL) – PRESENTATION LAYER – clubs
 * The OpenAPI (Swagger) documentation of these routes is in public/v1/docs/paths/ClubPaths.php.
 */

Flight::route('GET /clubs', function () {
    Flight::json(Flight::club_service()->get_all());
});

Flight::route('GET /clubs/@id:[0-9]+', function ($id) {
    Flight::json(Flight::club_service()->get_by_id($id));
});

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
