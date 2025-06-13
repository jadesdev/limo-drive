<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

trait ApiResponse
{
    /**
     * Send a success response.
     *
     * @param  array|Collection|AnonymousResourceCollection  $data
     * @param  string  $message
     */
    protected function successResponse($message = 'Request Successful', $data = [], $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (! empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Send a data filed response.
     *
     * @param  array|Collection|AnonymousResourceCollection  $data
     * @param  string  $message
     */
    protected function dataResponse($message, $data, $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Send an error response.
     *
     * @param  string  $message
     * @param  int  $code
     */
    protected function errorResponse($message = 'Error', $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (! is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Not Found Response
     *
     * @param  string  $message
     */
    protected function notFoundResponse($message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Forbidden Response
     *
     * @param  string  $message
     */
    protected function forbiddenResponse($message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Paginated response
     *
     * @param  array|Collection|AnonymousResourceCollection  $items
     * @param  \Illuminate\Pagination\LengthAwarePaginator  $pagination
     */
    protected function paginatedResponse(string $message, $items, $pagination, int $code = 200): JsonResponse
    {
        $pagination->appends(request()->except('page'));

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $items,
            'pagination' => [
                'total' => $pagination->total(),
                'current_page' => $pagination->currentPage(),
                'current_items' => $pagination->count(),
                'previous_page' => $pagination->previousPageUrl(),
                'next_page' => $pagination->nextPageUrl(),
                'last_page' => $pagination->lastPage(),
                'per_page' => $pagination->perPage(),
                'first_page' => $pagination->url(1),
                'path' => $pagination->path(),
            ],
        ], $code);
    }

    /**
     * Cursor paginated response
     *
     * @param  \Illuminate\Pagination\CursorPaginator  $pagination
     */
    protected function cursorPaginatedResponse(string $message, $pagination, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $pagination->items(),
            'pagination' => [
                'per_page' => $pagination->perPage(),
                'next_cursor' => optional($pagination->nextCursor())->encode(),
                'prev_cursor' => optional($pagination->previousCursor())->encode(),
                'path' => $pagination->path(),
            ],
        ], $code);
    }

    /**
     * No Content Response
     */
    protected function noContentResponse(string $message = 'No content', int $code = 204): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], $code);
    }
}
