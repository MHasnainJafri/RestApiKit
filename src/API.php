<?php

namespace Mhasnainjafri\RestApiKit;

use Illuminate\Support\Facades\Cache;
use Mhasnainjafri\RestApiKit\Helpers\FileUploader;
use Mhasnainjafri\RestApiKit\Http\Responses\ResponseBuilder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

class API
{
    // Success Codes
    const SUCCESS = 200;

    const CREATED = 201;

    const NO_CONTENT = 204;

    // Client Error Codes
    const BAD_REQUEST = 400;

    const UNAUTHORIZED = 401;

    const FORBIDDEN = 403;

    const NOT_FOUND = 404;

    const METHOD_NOT_ALLOWED = 405;

    const UNPROCESSABLE_ENTITY = 422;

    // Server Error Codes
    const INTERNAL_SERVER_ERROR = 500;

    const NOT_IMPLEMENTED = 501;

    const BAD_GATEWAY = 502;

    const SERVICE_UNAVAILABLE = 503;

    public static function response($data = null, string|array|null $message = null, int $status = 200): JsonResponse
    {
        $responseBuilder = new ResponseBuilder($message, $status);
        $responseBuilder->data($data);

        return $responseBuilder->toResponse();
    }

    public static function exception($exception, $message, $status): JsonResponse
    {
        $responseBuilder = new ResponseBuilder(status: $status);

        // Build a more detailed response with meaningful information
        return $responseBuilder
            ->message($message ?? 'Something went Wrong')
            ->line('Error occurred on line '.$exception->getLine()) // Specific line of code where the error occurred
            ->file('In file: '.$exception->getFile()) // The file in which the error occurred
            ->stack('Error stack trace: '.$exception->getTraceAsString()) // Full stack trace for debugging
            ->addError('Specific error: '.$exception->getMessage()) // More detailed error message, using the exception message
            ->addError('Request ID: '.uniqid()) // Unique request ID to trace the issue
            ->toResponse(); // Finalize and return the response

    }

    public static function errors(array $errors, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'errors' => $errors,
        ], $status);
    }

    public static function notFound(string $message, int $status = self::NOT_FOUND): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    public static function cacheResponse(string $key, callable $callback, ?int $minutes = null): JsonResponse
    {
        $minutes = $minutes ?? config('restify.cache.default_ttl', 60);

        // Cache the data if it doesn't exist
        $data = Cache::remember($key, $minutes, $callback);

        return response()->json($data);
    }

    public static function clearCacheKey(string $cacheKey): bool
    {
        return Cache::forget($cacheKey);
    }

    /**
     * Upload a file using FileUploader.
     *
     * @return string The file path
     *
     * @throws \Exception
     */
    public static function upload(UploadedFile $file, ?string $path = null, ?string $disk = null): string
    {
        return FileUploader::upload($file, $path, $disk);
    }

    /**
     * Delete a file.
     */
    public static function deleteFile(string $filePath, ?string $disk = null): bool
    {
        return FileUploader::delete($filePath, $disk);
    }

    /**
     * Get the URL for a stored file.
     */
    public static function fileUrl(string $filePath, ?string $disk = null): string
    {
        return FileUploader::url($filePath, $disk);
    }

    /**
     * Return a validation error response
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function validationError(array $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => self::UNPROCESSABLE_ENTITY,
        ], 422);
    }
}
