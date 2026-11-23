<?php
/**
 * VERSION 1 – routes the way many first projects look (DO NOT DO THIS IN YOUR PROJECT)
 *
 * Problems:
 *  - verbs in the URL (/get_events, /add_event, /delete_event) instead of resources + HTTP methods
 *  - the id travels as a query parameter even where it identifies the resource
 *  - delete with POST (GET/POST for everything)
 *  - ALWAYS status 200 – even for errors; the client has to look inside the JSON
 *
 *   GET  /get_events
 *   GET  /event_by_id?id=1
 *   POST /add_event
 *   POST /delete_event?id=1
 */

Flight::route('GET /get_events', function () {
    Flight::json(Flight::event_service_v2()->get_all());
});

Flight::route('GET /event_by_id', function () {
    $id = Flight::request()->query['id'];
    Flight::json(Flight::event_service_v2()->get_by_id($id));       // 200 even when not found
});

Flight::route('POST /add_event', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::event_service_v2()->add($data));            // 200 even when invalid
});

Flight::route('POST /delete_event', function () {
    $id = Flight::request()->query['id'];
    Flight::json(Flight::event_service_v2()->delete($id));
});
