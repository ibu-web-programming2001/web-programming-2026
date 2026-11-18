<?php
/*
 * Lab 06 – checker for Tasks 1 to 5.          Run from the backend folder:   php public/v1/docs/test_tasks.php
 *
 * The script does two things:
 *   1. it generates the OpenAPI document from doc_setup.php and paths/*.php – exactly like swagger.php – and
 *      prints what is documented: paths, parameters, responses, schemas;
 *   2. it starts PHP's built-in web server by itself, sends REAL requests to your API and checks that every
 *      status code the API answers with is documented ("docs vs code").
 * Compare the output with the "Expected output" box of each task in the lab document.
 *
 * A section prints [TODO] until you write its documentation.
 * Everything the script adds is deleted again, so you can run it as often as you like.
 *
 * DO NOT CHANGE THIS FILE.
 */
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

const PORT = 8765;
$backend = realpath(__DIR__ . '/../../..');
$root = realpath($backend . '/..');                  // the week-06 folder (server.php is there)

if (!file_exists($backend . '/vendor/autoload.php')) {
    exit("[ERROR] backend/vendor is missing – run  composer install  in the backend folder (Task 1).\n");
}
require $backend . '/vendor/autoload.php';
require_once $backend . '/rest/dao/AnnouncementDao.php';

function title($text) { echo "\n=== $text ===\n"; }
function todo($task, $what) { echo "[TODO] $task – not documented yet: $what\n"; }
function line($label, $text) { echo str_pad("$label ", 60, '.') . " $text\n"; }

/* ---------- 1. generate the OpenAPI document (the same steps as swagger.php) ---------- */

function generate_spec()
{
    if (!class_exists(\OpenApi\Generator::class)) {
        return [null, 'swagger-php is not installed – run  composer require zircote/swagger-php  in the backend folder (Task 1).'];
    }
    if (!defined('API_BASE_URL')) {
        define('API_BASE_URL', 'http://127.0.0.1:' . PORT . '/backend/');
    }
    $files = array_merge([__DIR__ . '/doc_setup.php'], glob(__DIR__ . '/paths/*.php'));
    $warnings = [];
    set_error_handler(function ($no, $message) use (&$warnings) {
        $warnings[] = $message;
        return true;
    });
    try {
        foreach ($files as $file) {
            require_once $file;
        }
        $openapi = (new \OpenApi\Generator())->generate($files);
        $spec = json_decode($openapi->toJson(), true);
    } catch (Throwable $e) {
        restore_error_handler();
        $where = str_replace(realpath(__DIR__ . '/../../..') . DIRECTORY_SEPARATOR, '', $e->getFile());
        return [null, get_class($e) . " in $where, line {$e->getLine()}: " . $e->getMessage()];
    }
    restore_error_handler();
    foreach (array_unique($warnings) as $warning) {
        echo "[WARNING] swagger-php: $warning\n";
    }
    return [$spec, null];
}

/** The operation for METHOD + PATH, or null. */
function op($spec, $method, $path)
{
    return $spec['paths'][$path][strtolower($method)] ?? null;
}

function documented_statuses($operation)
{
    return array_map('intval', array_keys($operation['responses'] ?? []));
}

/** "id (path, integer)" for every parameter of an operation. */
function params_text($operation, $in = null)
{
    $list = [];
    foreach ($operation['parameters'] ?? [] as $p) {
        if ($in !== null && ($p['in'] ?? '') !== $in) continue;
        $list[] = ($p['name'] ?? '?') . ' (' . ($p['in'] ?? '?') . ', ' . ($p['schema']['type'] ?? '?') . ')';
    }
    return $list ? implode(', ', $list) : 'none';
}

/** One line per documented operation: statuses, tags and parameters. */
function show_op($spec, $method, $path)
{
    $o = op($spec, $method, $path);
    line("$method $path", 'responses: ' . implode(', ', documented_statuses($o)) . '   tags: ' . implode(', ', $o['tags'] ?? []));
    echo str_repeat(' ', 4) . 'parameters: ' . params_text($o) . "\n";
    if (isset($o['requestBody'])) {
        $ref = $o['requestBody']['content']['application/json']['schema']['$ref'] ?? 'no schema';
        echo str_repeat(' ', 4) . 'request body: ' . basename($ref) . "\n";
    }
}

/** Names of the properties of a schema (including the ones it takes over with allOf). */
function schema_props($spec, $name)
{
    $schema = $spec['components']['schemas'][$name] ?? null;
    if ($schema === null) return null;
    $props = array_keys($schema['properties'] ?? []);
    foreach ($schema['allOf'] ?? [] as $part) {
        if (isset($part['$ref'])) {
            $props = array_merge(schema_props($spec, basename($part['$ref'])) ?? [], $props);
        }
        $props = array_merge($props, array_keys($part['properties'] ?? []));
    }
    return array_values(array_unique($props));
}

/* ---------- 2. the API under test ---------- */

function start_server($root, $port)
{
    $null = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
    $process = proc_open(
        [PHP_BINARY, '-S', "127.0.0.1:$port", 'server.php'],
        [0 => ['file', $null, 'r'], 1 => ['file', $null, 'w'], 2 => ['file', $null, 'w']],
        $pipes,
        $root
    );
    for ($i = 0; $i < 50; $i++) {
        $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.1);
        if ($socket) {
            fclose($socket);
            return $process;
        }
        usleep(100000);
    }
    proc_terminate($process);
    exit("[ERROR] Could not start the web server on port $port. Is the port already in use?\n");
}

/** Send a request and return [status code, decoded JSON or raw text]. */
function request($method, $path, $body = null)
{
    $options = ['method' => $method, 'ignore_errors' => true, 'timeout' => 10, 'header' => "Accept: application/json\r\n"];
    if ($body !== null) {
        $options['header'] .= "Content-Type: application/json\r\n";
        $options['content'] = json_encode($body);
    }
    $text = @file_get_contents('http://127.0.0.1:' . PORT . "/backend$path", false, stream_context_create(['http' => $options]));
    $headers = function_exists('http_get_last_response_headers') ? http_get_last_response_headers() : ($http_response_header ?? []);
    preg_match('/\s(\d{3})\s?/', $headers[0] ?? '', $match);
    $json = json_decode((string) $text, true);
    return [(int) ($match[1] ?? 0), $json === null ? (string) $text : $json];
}

/**
 * Docs vs code: send a real request and check that its status is documented for $doc_path.
 *   GET /categories/999/events ......... 404  documented
 */
function real($spec, $method, $url, $doc_path, $body = null)
{
    [$status, $answer] = request($method, $url, $body);
    $o = op($spec, $method, $doc_path);
    $verdict = in_array($status, documented_statuses($o), true)
        ? 'documented'
        : 'NOT DOCUMENTED (documented: ' . implode(', ', documented_statuses($o)) . ')';
    echo '    ' . str_pad("$method $url ", 56, '.') . " $status  $verdict\n";
    return [$status, $answer];
}

function clean_up()
{
    BaseDao::connection()->exec("DELETE FROM announcements WHERE title LIKE 'Checker:%'");
}

/* ================================================================================================ */

[$spec, $error] = generate_spec();

title('Task 1 – swagger-php and Swagger UI');
if ($spec === null) {
    echo "[ERROR] The OpenAPI document cannot be generated.\n        $error\n";
    exit(1);
}
$version = \Composer\InstalledVersions::getPrettyVersion('zircote/swagger-php');
line('swagger-php', 'version ' . explode('.', ltrim($version, 'v'))[0] . '.x installed');
line('OpenAPI document', 'OpenAPI ' . $spec['openapi'] . ', ' . count($spec['paths']) . ' paths, '
    . count($spec['components']['schemas'] ?? []) . ' schemas');

clean_up();
$server = start_server($root, PORT);

try {
    [$status, $page] = request('GET', '/public/v1/docs/');
    line('GET /public/v1/docs/', $status . (is_string($page) && str_contains($page, 'swagger-ui') ? '  Swagger UI' : '  not the Swagger UI page'));
    [$status, $json] = request('GET', '/public/v1/docs/swagger.php');
    line('GET /public/v1/docs/swagger.php', $status . (is_array($json) && isset($json['openapi']) ? '  OpenAPI JSON, ' . count($json['paths']) . ' paths' : '  not JSON'));

    /* ================= Task 2 ================= */
    title('Task 2 – schemas');
    $error_props = schema_props($spec, 'Error');
    if (!in_array('status', $error_props ?? [], true)) {
        todo('Task 2.1', "the property 'status' of the Error schema");
    } else {
        line('Error', 'properties: ' . implode(', ', $error_props));
        [$status, $answer] = request('GET', '/clubs/999');
        $same = is_array($answer) && array_keys($answer) == $error_props;
        echo '    ' . str_pad('GET /clubs/999 ', 56, '.') . " $status  " . json_encode($answer, JSON_UNESCAPED_UNICODE)
            . ($same ? '  matches the schema' : '  DOES NOT match the schema') . "\n";
    }
    $input = $spec['components']['schemas']['AnnouncementInput'] ?? null;
    $output = $spec['components']['schemas']['Announcement'] ?? null;
    if ($input === null || $output === null) {
        todo('Task 2.2', 'the schemas AnnouncementInput and Announcement');
    } else {
        line('AnnouncementInput', 'required: ' . implode(', ', $input['required'] ?? []));
        echo '    properties: ' . implode(', ', schema_props($spec, 'AnnouncementInput')) . "\n";
        $own = array_diff(schema_props($spec, 'Announcement'), schema_props($spec, 'AnnouncementInput'));
        $uses = isset($output['allOf'][0]['$ref']) ? basename($output['allOf'][0]['$ref']) . ' + ' : '';
        line('Announcement', $uses . implode(', ', $own));
    }

    /* ================= Task 3 ================= */
    title('Task 3 – nested resources');
    if (op($spec, 'GET', '/categories/{id}/events') === null) {
        todo('Task 3.1', 'GET /categories/{id}/events');
    } else {
        show_op($spec, 'GET', '/categories/{id}/events');
        real($spec, 'GET', '/categories/2/events', '/categories/{id}/events');
        real($spec, 'GET', '/categories/999/events', '/categories/{id}/events');
    }
    if (op($spec, 'GET', '/clubs/{id}/events') === null) {
        todo('Task 3.2', 'GET /clubs/{id}/events');
    } else {
        show_op($spec, 'GET', '/clubs/{id}/events');
        real($spec, 'GET', '/clubs/2/events', '/clubs/{id}/events');
        real($spec, 'GET', '/clubs/999/events', '/clubs/{id}/events');
    }

    /* ================= Task 4 ================= */
    title('Task 4 – query parameters of GET /events');
    $events = op($spec, 'GET', '/events');
    if (params_text($events, 'query') === 'none') {
        todo('Task 4', 'the query parameters of GET /events');
    } else {
        line('GET /events', 'responses: ' . implode(', ', documented_statuses($events)) . '   summary: ' . ($events['summary'] ?? ''));
        echo '    query parameters: ' . params_text($events, 'query') . "\n";
        real($spec, 'GET', '/events', '/events');
        real($spec, 'GET', '/events?search=night', '/events');
        real($spec, 'GET', '/events?search=a', '/events');
        real($spec, 'GET', '/events?category_id=2&only_upcoming=1&min_free_seats=1', '/events');
        real($spec, 'GET', '/events?min_free_seats=-5', '/events');
        real($spec, 'GET', '/events?club_id=999', '/events');
    }

    /* ================= Task 5 ================= */
    title('Task 5 – announcements');
    if (op($spec, 'GET', '/announcements') === null) {
        todo('Task 5', 'AnnouncementPaths.php');
    } else {
        $operations = [
            ['GET', '/announcements'], ['GET', '/announcements/{id}'], ['GET', '/clubs/{id}/announcements'],
            ['POST', '/announcements'], ['PUT', '/announcements/{id}'], ['DELETE', '/announcements/{id}'],
        ];
        foreach ($operations as [$method, $path]) {
            if (op($spec, $method, $path) === null) {
                line("$method $path", 'NOT DOCUMENTED');
            } else {
                show_op($spec, $method, $path);
            }
        }
        echo "\n";
        $exists = fn($m, $p) => op($spec, $m, $p) !== null;
        if ($exists('GET', '/announcements')) {
            real($spec, 'GET', '/announcements', '/announcements');
            real($spec, 'GET', '/announcements?limit=2', '/announcements');
            real($spec, 'GET', '/announcements?limit=50', '/announcements');
        }
        if ($exists('GET', '/announcements/{id}')) {
            real($spec, 'GET', '/announcements/1', '/announcements/{id}');
            real($spec, 'GET', '/announcements/999', '/announcements/{id}');
        }
        if ($exists('GET', '/clubs/{id}/announcements')) {
            real($spec, 'GET', '/clubs/1/announcements', '/clubs/{id}/announcements');
            real($spec, 'GET', '/clubs/999/announcements', '/clubs/{id}/announcements');
        }
        $id = 0;
        if ($exists('POST', '/announcements')) {
            real($spec, 'POST', '/announcements', '/announcements', ['club_id' => 1, 'title' => 'Checker: second pin', 'body' => 'This one must not be saved.', 'is_pinned' => true]);
            [$status, $created] = real($spec, 'POST', '/announcements', '/announcements', ['club_id' => 4, 'title' => 'Checker: debate on Friday', 'body' => 'Room B-103 at 18:00, everybody is welcome.', 'is_pinned' => false]);
            $id = $created['id'] ?? 0;
        }
        if ($exists('PUT', '/announcements/{id}') && $id) {
            real($spec, 'PUT', "/announcements/$id", '/announcements/{id}', ['club_id' => 4, 'title' => 'Checker: debate moved to Monday', 'body' => 'Same room, same time, new day.']);
            real($spec, 'PUT', '/announcements/999', '/announcements/{id}', ['club_id' => 4, 'title' => 'Checker: nothing', 'body' => 'This announcement does not exist.']);
        }
        if ($exists('DELETE', '/announcements/{id}') && $id) {
            real($spec, 'DELETE', "/announcements/$id", '/announcements/{id}');
            real($spec, 'DELETE', "/announcements/$id", '/announcements/{id}');
        }
    }
} finally {
    proc_terminate($server);
    clean_up();
}
