<?php
use OpenApi\Attributes as OA;

/*
 * OpenAPI documentation of rest/routes/UserRoutes.php.
 * swagger-php reads the #[OA\...] attributes; the methods are empty – they only carry the documentation.
 * Keep it in sync with the routes: one method per endpoint.
 */
class UserPaths
{
    #[OA\Get(
        path: '/users',
        tags: ['users'],
        summary: 'List all users',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of users',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/User'))
            )
        ]
    )]
    public function getUsers() {}

    #[OA\Get(
        path: '/users/{id}',
        tags: ['users'],
        summary: 'Get one user',
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
                description: 'The user',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function getUsersById() {}

    #[OA\Post(
        path: '/users',
        tags: ['users'],
        summary: 'Create a user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UserInput')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created user',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid data',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function postUsers() {}

    #[OA\Put(
        path: '/users/{id}',
        tags: ['users'],
        summary: 'Update a user',
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
            content: new OA\JsonContent(ref: '#/components/schemas/UserInput')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated user',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid data',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function putUsersById() {}

    #[OA\Delete(
        path: '/users/{id}',
        tags: ['users'],
        summary: 'Delete a user',
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
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function deleteUsersById() {}
}
