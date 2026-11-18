<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/EventRoutes.php.
 * swagger-php reads the #[OA\...] attributes; the methods are empty – they only carry the documentation.
 * Keep it in sync with the routes: one method per endpoint.
 */
class EventPaths
{
    /*
     * TODO Task 4 – GET /events understands optional QUERY parameters since Lab 5 (Task 4.2),
     * but the documentation below still describes only the plain list. Add:
     *   1. six query parameters (in: 'query', required: false), each with a description, an example and a schema:
     *        search          string    'night'      text in the title or description (2–50 characters)
     *        club_id         integer   2            only the events of this club
     *        category_id     integer   2            only the events of this category
     *        city            string    'Sarajevo'   only the events in this city
     *        only_upcoming   integer   1            1 = only events that have not started yet
     *        min_free_seats  integer   1            only events with at least this many free seats
     *   2. the two answers the route can give besides 200 – both with the Error schema:
     *        400  'Invalid search text or filter'      (?search=a, ?min_free_seats=-5)
     *        404  'Club or category not found'         (?club_id=999)
     *   Change the summary to 'List, search or filter events'.
     */
    #[OA\Get(
        path: '/events',
        tags: ['events'],
        summary: 'List, search or filter events',
        description: 'With ?search= the other parameters are ignored. Without any parameter: all events.',
        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Text in the title or description (2–50 characters)',
                example: 'night',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'club_id',
                in: 'query',
                required: false,
                description: 'Only the events of this club',
                example: 2,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'category_id',
                in: 'query',
                required: false,
                description: 'Only the events of this category',
                example: 2,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'city',
                in: 'query',
                required: false,
                description: 'Only the events in this city',
                example: 'Sarajevo',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'only_upcoming',
                in: 'query',
                required: false,
                description: '1 = only events that have not started yet',
                example: 1,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'min_free_seats',
                in: 'query',
                required: false,
                description: 'Only events with at least this many free seats',
                example: 1,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of events',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Event'))
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid search text or filter',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Club or category not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function getEvents() {}

    #[OA\Get(
        path: '/events/{id}',
        tags: ['events'],
        summary: 'Get one event',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                example: 1,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The event',
                content: new OA\JsonContent(ref: '#/components/schemas/Event')
            ),
            new OA\Response(
                response: 404,
                description: 'Event not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function getEventsById() {}

    #[OA\Get(
        path: '/events/{id}/reviews',
        tags: ['events', 'reviews'],
        summary: 'All reviews of one event',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                example: 9,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reviews',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Review'))
            ),
            new OA\Response(
                response: 404,
                description: 'Event not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function getEventsByIdReviews() {}

    /*
     * TODO Task 3.2 – the events of one club (Lab 5, Task 4.3): GET /clubs/{id}/events
     *   tags ['clubs', 'events'], summary 'All events of one club', path parameter id (example 2),
     *   200 – an array of Event,  404 – 'Club not found' with the Error schema.
     *   Name the method getClubsByIdEvents().
     */
    #[OA\Get(
        path: '/clubs/{id}/events',
        tags: ['clubs', 'events'],
        summary: 'All events of one club',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                example: 2,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Events of the club',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Event'))
            ),
            new OA\Response(
                response: 404,
                description: 'Club not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function getClubsByIdEvents() {}

    #[OA\Post(
        path: '/events',
        tags: ['events'],
        summary: 'Create an event',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/EventInput')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created event',
                content: new OA\JsonContent(ref: '#/components/schemas/Event')
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid data',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function postEvents() {}

    #[OA\Put(
        path: '/events/{id}',
        tags: ['events'],
        summary: 'Update an event',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                example: 5,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/EventInput')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated event',
                content: new OA\JsonContent(ref: '#/components/schemas/Event')
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid data',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Event not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function putEventsById() {}

    #[OA\Delete(
        path: '/events/{id}',
        tags: ['events'],
        summary: 'Delete an event',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                example: 5,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Deleted',
                content: new OA\JsonContent(ref: '#/components/schemas/Message')
            ),
            new OA\Response(
                response: 400,
                description: 'Students are registered for this event',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Event not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function deleteEventsById() {}
}
