<?php

declare(strict_types=1);

namespace App\Support;

/**
 * User-facing wording for HTTP error statuses. Keep in sync with
 * resources/js/react/lib/http-status.ts (same codes, same text).
 */
final class HttpStatusMessage
{
    /** @var array<int, array{title: string, message: string}> */
    public const MESSAGES = [
        400 => ['title' => 'Bad Request', 'message' => 'The request was invalid or malformed. Please check what you entered and try again.'],
        401 => ['title' => 'Unauthorized', 'message' => 'You are not logged in. Please sign in to continue.'],
        402 => ['title' => 'Payment Required', 'message' => 'A subscription or payment is needed to use this feature.'],
        403 => ['title' => 'Forbidden', 'message' => 'You are logged in, but you do not have permission to do this.'],
        404 => ['title' => 'Not Found', 'message' => 'The page or record you are looking for does not exist.'],
        405 => ['title' => 'Method Not Allowed', 'message' => 'This action was sent the wrong way (for example GET instead of POST).'],
        409 => ['title' => 'Conflict', 'message' => 'This conflicts with existing data, such as a duplicate or a record that changed.'],
        419 => ['title' => 'Page Expired', 'message' => 'Your session expired. Refresh the page and try again.'],
        422 => ['title' => 'Unprocessable Content', 'message' => 'Some fields are invalid. Please check them and try again.'],
        429 => ['title' => 'Too Many Requests', 'message' => 'Too many requests. Please wait a moment and try again.'],
        500 => ['title' => 'Internal Server Error', 'message' => 'Something went wrong on our side. Please try again or contact IT Support.'],
        503 => ['title' => 'Service Unavailable', 'message' => 'The system is under maintenance. Please try again later.'],
    ];

    /** @return array{code: int, title: string, message: string} */
    public static function for(int $status): array
    {
        $known = self::MESSAGES[$status] ?? null;

        if ($known !== null) {
            return ['code' => $status, ...$known];
        }

        return $status >= 500
            ? ['code' => $status, ...self::MESSAGES[500], 'title' => 'Server Error']
            : ['code' => $status, ...self::MESSAGES[400], 'title' => 'Request Error'];
    }
}
