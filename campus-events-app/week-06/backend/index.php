<?php
/*
 * Entry point of the REST API.
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
require_once __DIR__ . '/rest/services/DashboardService.php';
require_once __DIR__ . '/rest/services/OpenMeteoService.php';
require_once __DIR__ . '/rest/services/OpenMeteoServicev1.php';
require_once __DIR__ . '/rest/services/EventServicev2.php';     // only for the v1 demo routes

// Register services once, use them in every route: Flight::event_service()->...
Flight::register('category_service', 'CategoryService');
Flight::register('club_service', 'ClubService');
Flight::register('event_service', 'EventService');
Flight::register('registration_service', 'RegistrationService');
Flight::register('review_service', 'ReviewService');
Flight::register('user_service', 'UserService');
Flight::register('dashboard_service', 'DashboardService');
Flight::register('open_meteo_service', 'OpenMeteoService');
Flight::register('open_meteo_service_v1', 'OpenMeteoServicev1');
Flight::register('event_service_v2', 'EventServicev2');

/*
 * ONE place that turns every exception into a JSON response.
 *  - HttpException (thrown by services) -> its own status code and message (400, 404...)
 *  - anything else (PDOException, bugs)  -> 500 with a generic message.
 *    Never send stack traces, SQL or file paths to the client!
 */
Flight::map('error', function (Throwable $e) {
    if ($e instanceof HttpException) {
        $status = $e->get_status();
        $message = $e->getMessage();
    } else {
        $status = 500;
        $message = 'Something went wrong on the server. Please try again later.';
        error_log($e);   // details only in the server log
    }
    Flight::response()
        ->clearBody()
        ->status($status)
        ->header('Content-Type', 'application/json')
        ->write(json_encode(['error' => $message]))
        ->send();
});

Flight::map('notFound', function () {
    Flight::response()
        ->clearBody()
        ->status(404)
        ->header('Content-Type', 'application/json')
        ->write(json_encode(['error' => 'Endpoint not found.']))
        ->send();
});

Flight::route('GET /', function () {
    Flight::json(['app' => 'CampusEvents API', 'message' => 'The backend is running.', 'docs' => 'public/v1/docs/']);
});

/* ---------- Step-by-step versions (can be called side by side) ---------- */

// VERSION 1: /get_events, /event_by_id?id=1, /add_event, /delete_event?id=1  – always 200
require_once __DIR__ . '/rest/routes/EventRoutesv1.php';

// VERSION 2: /v2/events... – REST + status codes, try/catch in every route
Flight::group('/v2', function () {
    require __DIR__ . '/rest/routes/EventRoutesv2.php';
});

/* ---------- VERSION 3 (FINAL): all entities, errors handled by Flight::map('error') above ---------- */

require_once __DIR__ . '/rest/routes/CategoryRoutes.php';
require_once __DIR__ . '/rest/routes/ClubRoutes.php';
require_once __DIR__ . '/rest/routes/EventRoutes.php';
require_once __DIR__ . '/rest/routes/RegistrationRoutes.php';
require_once __DIR__ . '/rest/routes/ReviewRoutes.php';
require_once __DIR__ . '/rest/routes/UserRoutes.php';
require_once __DIR__ . '/rest/routes/DashboardRoutes.php';
require_once __DIR__ . '/rest/routes/WeatherRoutes.php';

Flight::start();
