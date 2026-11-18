<?php
use OpenApi\Attributes as OA;

/*
 * General API information + reusable schemas (referenced from the paths with ref: '#/components/schemas/...').
 * swagger-php 6 reads these PHP 8 attributes – the class itself is empty.
 * (Plain /* comments on purpose: swagger-php would turn a /** doc comment into a description.)
 */
#[OA\Info(
    title: 'CampusEvents API',
    description: 'REST API of CampusEvents – student events at IBU – IT 2001 Web Programming, Lab 6.',
    version: '1.0',
    contact: new OA\Contact(name: 'Web Programming', email: 'web2001programming@gmail.com')
)]
#[OA\Server(url: API_BASE_URL, description: 'This server')]
/*
 * TODO Task 2.1 – the Error schema must describe what OUR error handler sends (Lab 5, Task 2.2):
 *   {"status": 400, "error": "Field 'title' is required."}
 *   Add the property 'status' (type integer, example 400) BEFORE 'error'.
 */
#[OA\Schema(
    schema: 'Error',
    properties: [
        new OA\Property(property: 'status', type: 'integer', example: 400),
        new OA\Property(property: 'error', type: 'string', example: 'Field \'title\' is required.')
    ]
)]
#[OA\Schema(
    schema: 'Message',
    properties: [new OA\Property(property: 'message', type: 'string', example: 'Event deleted.')]
)]
#[OA\Schema(
    schema: 'CategoryInput',
    required: ['name'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Workshops'),
        new OA\Property(property: 'description', type: 'string', example: 'Hands-on sessions')
    ]
)]
#[OA\Schema(
    schema: 'Category',
    allOf: [new OA\Schema(ref: '#/components/schemas/CategoryInput')],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'event_count', type: 'integer', example: 2)
    ]
)]
#[OA\Schema(
    schema: 'ClubInput',
    required: ['name'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Photography Club'),
        new OA\Property(property: 'faculty', type: 'string', example: 'FASS'),
        new OA\Property(property: 'founded_year', type: 'integer', example: 2021),
        new OA\Property(property: 'description', type: 'string', example: 'Photo walks and exhibitions.')
    ]
)]
#[OA\Schema(
    schema: 'Club',
    allOf: [new OA\Schema(ref: '#/components/schemas/ClubInput')],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 6),
        new OA\Property(property: 'event_count', type: 'integer', example: 1)
    ]
)]
#[OA\Schema(
    schema: 'EventInput',
    required: ['title', 'club_id', 'category_id', 'location', 'starts_at', 'capacity'],
    properties: [
        new OA\Property(property: 'title', type: 'string', example: 'Intro to Docker Workshop'),
        new OA\Property(property: 'club_id', type: 'integer', example: 2),
        new OA\Property(property: 'category_id', type: 'integer', example: 2),
        new OA\Property(property: 'location', type: 'string', example: 'FENS Lab 1'),
        new OA\Property(property: 'city', type: 'string', example: 'Sarajevo'),
        new OA\Property(
            property: 'starts_at',
            type: 'string',
            example: '2026-12-03 17:00',
            description: 'YYYY-MM-DD HH:MM (or HTML datetime-local YYYY-MM-DDTHH:MM)'
        ),
        new OA\Property(property: 'capacity', type: 'integer', example: 25),
        new OA\Property(
            property: 'description',
            type: 'string',
            example: 'Containers for your web project – bring a laptop.'
        )
    ]
)]
#[OA\Schema(
    schema: 'Event',
    allOf: [new OA\Schema(ref: '#/components/schemas/EventInput')],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'available_seats', type: 'integer', example: 198),
        new OA\Property(property: 'club_name', type: 'string', example: 'IBU Music Club'),
        new OA\Property(property: 'category_name', type: 'string', example: 'Music'),
        new OA\Property(property: 'is_upcoming', type: 'integer', example: 1),
        new OA\Property(property: 'average_rating', type: 'number', example: 4.5),
        new OA\Property(property: 'review_count', type: 'integer', example: 2)
    ]
)]
#[OA\Schema(
    schema: 'RegistrationInput',
    required: ['event_id'],
    properties: [
        new OA\Property(
            property: 'user_id',
            type: 'integer',
            example: 3,
            description: 'Student who registers'
        ),
        new OA\Property(property: 'event_id', type: 'integer', example: 12)
    ]
)]
#[OA\Schema(
    schema: 'Registration',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 2),
        new OA\Property(property: 'event_id', type: 'integer', example: 1),
        new OA\Property(property: 'registered_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'cancelled_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['registered', 'cancelled', 'attended']),
        new OA\Property(property: 'event_title', type: 'string', example: 'Welcome Concert'),
        new OA\Property(property: 'event_starts_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'event_location', type: 'string', example: 'IBU Amphitheatre'),
        new OA\Property(property: 'user_name', type: 'string', example: 'Ema Kovač'),
        new OA\Property(property: 'is_upcoming', type: 'integer', example: 1)
    ]
)]
#[OA\Schema(
    schema: 'ReviewInput',
    required: ['event_id', 'rating'],
    properties: [
        new OA\Property(property: 'user_id', type: 'integer', example: 3),
        new OA\Property(property: 'event_id', type: 'integer', example: 9),
        new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5, example: 5),
        new OA\Property(property: 'comment', type: 'string', example: 'Loved it!')
    ]
)]
#[OA\Schema(
    schema: 'Review',
    allOf: [new OA\Schema(ref: '#/components/schemas/ReviewInput')],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_name', type: 'string', example: 'Lana Mehić'),
        new OA\Property(property: 'event_title', type: 'string', example: 'Open Mic Night')
    ]
)]
#[OA\Schema(
    schema: 'UserInput',
    required: ['first_name', 'last_name', 'email', 'role'],
    properties: [
        new OA\Property(property: 'first_name', type: 'string', example: 'Amar'),
        new OA\Property(property: 'last_name', type: 'string', example: 'Hodžić'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'amar@campusevents.ba'),
        new OA\Property(
            property: 'password',
            type: 'string',
            format: 'password',
            example: 'Secret123',
            description: 'Required when creating, optional when updating'
        ),
        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'student'], example: 'student'),
        new OA\Property(
            property: 'faculty',
            type: 'string',
            enum: ['FENS', 'FASS', 'FBA', 'FEDU'],
            example: 'FENS'
        )
    ]
)]
#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 2),
        new OA\Property(property: 'first_name', type: 'string', example: 'Ema'),
        new OA\Property(property: 'last_name', type: 'string', example: 'Kovač'),
        new OA\Property(property: 'email', type: 'string', example: 'ema@campusevents.ba'),
        new OA\Property(property: 'role', type: 'string', example: 'student'),
        new OA\Property(property: 'faculty', type: 'string', example: 'FENS'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time')
    ]
)]
/*
 * TODO Task 2.2 – two schemas for the announcements (the table from Lab 3, database/announcements.sql):
 *   AnnouncementInput – what the client SENDS with POST and PUT:
 *       required: club_id, title, body
 *       club_id (integer, example 4), title (string, example 'Debate on Friday'),
 *       body (string, example 'Room B-103 at 18:00, everybody is welcome.'), is_pinned (boolean, example false)
 *   Announcement – what the API RETURNS: everything from AnnouncementInput (allOf + ref, as Event does) plus
 *       id (integer, example 1), club_name (string, example 'IBU Music Club'),
 *       created_at (string, format 'date-time')
 */
#[OA\Schema(
    schema: 'AnnouncementInput',
    required: ['club_id', 'title', 'body'],
    properties: [
        new OA\Property(property: 'club_id', type: 'integer', example: 4),
        new OA\Property(property: 'title', type: 'string', example: 'Debate on Friday'),
        new OA\Property(property: 'body', type: 'string', example: 'Room B-103 at 18:00, everybody is welcome.'),
        new OA\Property(property: 'is_pinned', type: 'boolean', example: false)
    ]
)]
#[OA\Schema(
    schema: 'Announcement',
    allOf: [new OA\Schema(ref: '#/components/schemas/AnnouncementInput')],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'club_name', type: 'string', example: 'IBU Music Club'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time')
    ]
)]
class OpenApiSpec {}
