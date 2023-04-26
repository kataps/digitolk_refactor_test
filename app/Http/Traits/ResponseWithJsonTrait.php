<?php

declare(strict_types=1);

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseWithJsonTrait
{
    /**
     * Return a success JSON response.
     *
     * @param string|null $message
     * @param mixed $data
     * @param int $status
     *
     * @return JsonResponse
     */
    public function successJsonResponse(?string $message = null,  $data = null, int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Return an error JSON response.
     *
     * @param string|null $message
     * @param int $status
     *
     * @return JsonResponse
     */
    public function errorJsonResponse(?string $message = null, int $status = 400): JsonResponse
    {
        $response = [
            'success' => false,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a validation error JSON response.
     *
     * @param array $errors
     * @param int $status
     *
     * @return JsonResponse
     */
    public function validationErrorJsonResponse(array $errors, int $status = 422): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'The given data was invalid.',
            'errors' => $errors,
        ], $status);
    }

    /**
     * Return a forbidden JSON response.
     *
     * @param string|null $message
     *
     * @return JsonResponse
     */
    public function forbiddenJsonResponse(?string $message = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message ?? 'You are not authorized to perform this action.',
        ], 403);
    }
}
