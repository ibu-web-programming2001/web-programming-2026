<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/ClubRoutes.php.
 * swagger-php reads the #[OA\...] attributes; the methods are empty – they only carry the documentation.
 * Keep it in sync with the routes: one method per endpoint.
 */
class ClubPaths
{
    #[OA\Get(
        path: '/clubs',
        tags: ['clubs'],
        summary: 'List all clubs',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of clubs',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Club'))
            )
        ]
    )]
    public function getClubs() {}

    #[OA\Get(
        path: '/clubs/{id}',
        tags: ['clubs'],
        summary: 'Get one club',
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
                description: 'The club',
                content: new OA\JsonContent(ref: '#/components/schemas/Club')
            ),
            new OA\Response(
                response: 404,
                description: 'Club not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function getClubsById() {}

    #[OA\Post(
        path: '/clubs',
        tags: ['clubs'],
        summary: 'Create a club',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ClubInput')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created club',
                content: new OA\JsonContent(ref: '#/components/schemas/Club')
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid data',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function postClubs() {}

    #[OA\Put(
        path: '/clubs/{id}',
        tags: ['clubs'],
        summary: 'Update a club',
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
            content: new OA\JsonContent(ref: '#/components/schemas/ClubInput')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated club',
                content: new OA\JsonContent(ref: '#/components/schemas/Club')
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid data',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Club not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function putClubsById() {}

    #[OA\Delete(
        path: '/clubs/{id}',
        tags: ['clubs'],
        summary: 'Delete a club',
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
                response: 400,
                description: 'Cannot be deleted (business rule)',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'Club not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function deleteClubsById() {}
}
