<?php
/*
 * Entry point of the REST API (the Presentation Layer – Lab 5).
 * The documentation of the API (OpenAPI / Swagger UI) is in public/v1/docs/ (Lab 6).
 *
 *   HTTP request -> routes (presentation) -> services (business logic) -> dao (data access) -> MySQL
 */
require __DIR__ . '/vendor/autoload.php';

if (!file_exists(__DIR__ . '/config/config.php')) {
    http_response_code(500);
    exit('Missing backend/config/config.php – copy config.example.php to config.php and set your database credentials.');
}

require_once __DIR__ . '/rest/services/CategoryService.php';
require_once __DIR__ . '/rest/services/ClubService.php';
require_once __DIR__ . '/rest/services/EventService.php';
require_once __DIR__ . '/rest/services/RegistrationService.php';
require_once __DIR__ . '/rest/services/ReviewService.php';
require_once __DIR__ . '/rest/services/UserService.php';
require_once __DIR__ . '/rest/services/AnnouncementService.php';

/*
 * Register every service ONCE. Flight creates the object the first time it is used,
 * and every route then calls it like this:  Flight::club_service()->get_all()
 */
Flight::register('club_service', 'ClubService');
Flight::register('registration_service', 'RegistrationService');
Flight::register('review_service', 'ReviewService');
Flight::register('user_service', 'UserService');

/* Lab 5 – Task 2.1 */
Flight::register('category_service', 'CategoryService');
Flight::register('event_service', 'EventService');
Flight::register('announcement_service', 'AnnouncementService');

/*
 * Lab 5 – Task 2.2: ONE place that turns every exception into a JSON response:
 *   {"status": 404, "error": "Club not found."}
 */
Flight::map('error', function (Throwable $e) {
    if ($e instanceof HttpException) {
        $status = $e->get_status();
        $message = $e->getMessage();
    } else {
        $status = 500;
        $message = 'Something went wrong on the server. Please try again later.';
        error_log($e);   // details only in the server log – never send SQL or file paths to the client
    }
    Flight::response()
        ->clearBody()
        ->status($status)
        ->header('Content-Type', 'application/json')
        ->write(json_encode(['status' => $status, 'error' => $message]))
        ->send();
});

/* Lab 5 – Task 2.3: a URL that matches no route:  {"status": 404, "error": "Endpoint GET /clubs/abc does not exist."} */
Flight::map('notFound', function () {
    $request = Flight::request();
    $path = strtok($request->url, '?');     // the URL without the query string
    Flight::response()
        ->clearBody()
        ->status(404)
        ->header('Content-Type', 'application/json')
        ->write(json_encode(['status' => 404, 'error' => "Endpoint {$request->method} $path does not exist."]))
        ->send();
});

Flight::route('GET /', function () {
    Flight::json(['app' => 'CampusEvents API', 'message' => 'The backend is running.', 'docs' => 'public/v1/docs/']);
});

require_once __DIR__ . '/rest/routes/ClubRoutes.php';
require_once __DIR__ . '/rest/routes/CategoryRoutes.php';
require_once __DIR__ . '/rest/routes/EventRoutes.php';
require_once __DIR__ . '/rest/routes/RegistrationRoutes.php';
require_once __DIR__ . '/rest/routes/ReviewRoutes.php';
require_once __DIR__ . '/rest/routes/UserRoutes.php';
require_once __DIR__ . '/rest/routes/AnnouncementRoutes.php';

Flight::start();
