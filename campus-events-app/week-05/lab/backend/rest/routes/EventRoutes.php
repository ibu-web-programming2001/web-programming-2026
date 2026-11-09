<?php
/**
 * PRESENTATION LAYER – events
 */

/*
 * TODO Task 4.1 – the five REST routes, exactly like ClubRoutes.php:
 *   GET    /events              -> get_all()                (Task 4.2 adds the query parameters)
 *   GET    /events/@id:[0-9]+   -> get_by_id($id)
 *   POST   /events              -> add($data)               201
 *   PUT    /events/@id:[0-9]+   -> update($data, $id)
 *   DELETE /events/@id:[0-9]+   -> delete($id), then {"message": "Event deleted."}
 *
 * TODO Task 4.2 – change GET /events so it understands optional QUERY parameters:
 *   $query = Flight::request()->query;
 *   1. ?search=night  -> if isset($query['search']):  answer with Flight::event_service()->search($query['search'])
 *                        and return; so that only one answer is sent
 *   2. ?club_id=2&min_free_seats=1 ...  -> keep only the allowed filter keys:
 *        $filters = array_intersect_key($query->getData(),
 *                       array_flip(['club_id', 'category_id', 'city', 'only_upcoming', 'min_free_seats']));
 *      if at least one filter is given (count($filters) > 0):  Flight::event_service()->filter($filters)
 *   3. no query parameters  -> Flight::event_service()->get_all()   (as in Task 4.1)
 */
Flight::route('GET /events', function () {
    $query = Flight::request()->query;

    if (isset($query['search'])) {
        Flight::json(Flight::event_service()->search($query['search']));
        return;
    }

    $filters = array_intersect_key($query->getData(), array_flip(['club_id', 'category_id', 'city', 'only_upcoming', 'min_free_seats']));
    if (count($filters) > 0) {
        Flight::json(Flight::event_service()->filter($filters));
    } else {
        Flight::json(Flight::event_service()->get_all());
    }
});

Flight::route('GET /events/@id:[0-9]+', function ($id) {
    Flight::json(Flight::event_service()->get_by_id($id));
});

/** Body: { "title": "...", "club_id": 2, "category_id": 2, "location": "...", "starts_at": "2026-12-01 18:00:00", "capacity": 30 } */
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

/*
 * TODO Task 4.3 – NESTED resources: things that belong to ONE event or club
 *   GET /events/@id:[0-9]+/reviews     -> Flight::review_service()->get_by_event($id)
 *   GET /clubs/@id:[0-9]+/events       -> Flight::event_service()->get_by_club($id)
 *   (The services answer 404 when the event or club does not exist.)
 */
Flight::route('GET /events/@id:[0-9]+/reviews', function ($id) {
    Flight::json(Flight::review_service()->get_by_event($id));
});

Flight::route('GET /clubs/@id:[0-9]+/events', function ($id) {
    Flight::json(Flight::event_service()->get_by_club($id));
});
