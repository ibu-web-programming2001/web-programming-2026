<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/AnnouncementRoutes.php.
 * swagger-php reads the #[OA\...] attributes; the methods are empty – they only carry the documentation.
 * Keep it in sync with the routes: one method per endpoint.
 */

/*
 * TODO Task 5 – document the six announcement routes from Lab 5 (Task 5). ClubPaths.php is the example.
 * Every endpoint gets tags: ['announcements'] and uses the schemas from Task 2.2 and the Error / Message schemas.
 *
 *   Method   Path                          Method name                  Parameters                      Responses
 *   GET      /announcements                getAnnouncements()           limit (query, integer, 1–20)    200 Announcement[], 400
 *   GET      /announcements/{id}           getAnnouncementsById()       id (path)                       200 Announcement, 404
 *   GET      /clubs/{id}/announcements     getClubsByIdAnnouncements()  id (path)                       200 Announcement[], 404
 *   POST     /announcements                postAnnouncements()          body AnnouncementInput          201 Announcement, 400
 *   PUT      /announcements/{id}           putAnnouncementsById()       id (path) + AnnouncementInput   200 Announcement, 400, 404
 *   DELETE   /announcements/{id}           deleteAnnouncementsById()    id (path)                       200 Message, 404
 *
 * GET /clubs/{id}/announcements gets the tags ['clubs', 'announcements'].
 */
class AnnouncementPaths
{
    #[OA\Get(
        path: '/announcements',
        tags: ['announcements'],
        summary: 'List all announcements, or only the newest ones',
        description: 'Pinned announcements come first. With ?limit= only the newest announcements, pinned or not.',
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: false,
                description: 'Only the newest announcements (1–20)',
                example: 3,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 20)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of announcements',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Announcement'))
            ),
            new OA\Response(
                response: 400,
                description: 'limit is not a number from 1 to 20',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function getAnnouncements() {}

    #[OA\Get(
        path: '/announcements/{id}',
        tags: ['announcements'],
        summary: 'Get one announcement',
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
                description: 'The announcement',
                content: new OA\JsonContent(ref: '#/components/schemas/Announcement')
            ),
            new OA\Response(
                response: 404,
                description: 'Announcement not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function getAnnouncementsById() {}

    #[OA\Get(
        path: '/clubs/{id}/announcements',
        tags: ['clubs', 'announcements'],
        summary: 'All announcements of one club',
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
                description: 'Announcements of the club',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Announcement'))
            ),
            new OA\Response(
                response: 404,
                description: 'Club not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function getClubsByIdAnnouncements() {}

    #[OA\Post(
        path: '/announcements',
        tags: ['announcements'],
        summary: 'Create an announcement',
        description: 'Rule: a club can have only one pinned announcement.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AnnouncementInput')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created announcement',
                content: new OA\JsonContent(ref: '#/components/schemas/Announcement')
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid data or a second pinned announcement',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function postAnnouncements() {}

    #[OA\Put(
        path: '/announcements/{id}',
        tags: ['announcements'],
        summary: 'Update an announcement',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                example: 1,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AnnouncementInput')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated announcement',
                content: new OA\JsonContent(ref: '#/components/schemas/Announcement')
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid data or a second pinned announcement',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Announcement not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function putAnnouncementsById() {}

    #[OA\Delete(
        path: '/announcements/{id}',
        tags: ['announcements'],
        summary: 'Delete an announcement',
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
                description: 'Deleted',
                content: new OA\JsonContent(ref: '#/components/schemas/Message')
            ),
            new OA\Response(
                response: 404,
                description: 'Announcement not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function deleteAnnouncementsById() {}
}
