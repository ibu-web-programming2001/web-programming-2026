<?php
/**
 * PRESENTATION LAYER – club announcements (the table from Lab 3, the service from Lab 4)
 */

/*
 * TODO Task 5 – the routes for announcements. Every route calls Flight::announcement_service():
 *
 *   GET    /announcements                   -> ?limit=3 given: get_latest($limit)   otherwise: get_all()
 *   GET    /announcements/@id:[0-9]+        -> get_by_id($id)
 *   GET    /clubs/@id:[0-9]+/announcements  -> get_by_club($id)                      (nested resource)
 *   POST   /announcements                   -> add($data)                            201
 *   PUT    /announcements/@id:[0-9]+        -> update($data, $id)
 *   DELETE /announcements/@id:[0-9]+        -> delete($id), then {"message": "Announcement deleted."}
 */

Flight::route('GET /announcements', function () {
    $limit = Flight::request()->query['limit'] ?? null;
    if ($limit !== null) {
        Flight::json(Flight::announcement_service()->get_latest($limit));
    } else {
        Flight::json(Flight::announcement_service()->get_all());
    }
});

Flight::route('GET /announcements/@id:[0-9]+', function ($id) {
    Flight::json(Flight::announcement_service()->get_by_id($id));
});

Flight::route('GET /clubs/@id:[0-9]+/announcements', function ($id) {
    Flight::json(Flight::announcement_service()->get_by_club($id));
});

/** Body: { "club_id": 4, "title": "Debate on Friday", "body": "Room B-103 at 18:00 ...", "is_pinned": true } */
Flight::route('POST /announcements', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::announcement_service()->add($data), 201);
});

Flight::route('PUT /announcements/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::announcement_service()->update($data, $id));
});

Flight::route('DELETE /announcements/@id:[0-9]+', function ($id) {
    Flight::announcement_service()->delete($id);
    Flight::json(['message' => 'Announcement deleted.']);
});
