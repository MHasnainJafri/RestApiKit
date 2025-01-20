<?php

namespace Mhasnainjafri\RestApiKit\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Mhasnainjafri\RestApiKit\Helpers\FileUploader;
use Mhasnainjafri\RestApiKit\Http\Responses\ResponseBuilder;

class RestController extends Controller
{
    protected function response($data = null, string|array $message = null, int $status = 200): JsonResponse
    {
        $responseBuilder = new ResponseBuilder($message, $status);
        $responseBuilder->data($data);
        return $responseBuilder->toResponse();
    }
    protected function exception($exception,$message,$status): JsonResponse
    {
        $responseBuilder = new ResponseBuilder(status: $status);

        // Build a more detailed response with meaningful information
        return $responseBuilder
            ->message($message??'Something went Wrong')
            ->line('Error occurred on line ' . $exception->getLine()) // Specific line of code where the error occurred
            ->file('In file: ' . $exception->getFile()) // The file in which the error occurred
            ->stack('Error stack trace: ' . $exception->getTraceAsString()) // Full stack trace for debugging
            ->addError('Specific error: ' . $exception->getMessage()) // More detailed error message, using the exception message
            ->addError('Request ID: ' . uniqid()) // Unique request ID to trace the issue
            ->toResponse(); // Finalize and return the response
        
        
    }

    protected function errors(array $errors, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'errors' => $errors,
        ], $status);
    }

    protected function cacheResponse(string $key, callable $callback, ?int $minutes = null): JsonResponse
    {
        $minutes = $minutes ?? config('restify.cache.default_ttl', 60);

        // Cache the data if it doesn't exist
        $data = Cache::remember($key, $minutes, $callback);

        return response()->json($data);
    }

    /**
     * Upload a file using FileUploader.
     *
     * @return string The file path
     *
     * @throws \Exception
     */
    public function upload(UploadedFile $file, ?string $path = null, ?string $disk = null): string
    {
        return FileUploader::upload($file, $path, $disk);
    }

    /**
     * Delete a file.
     */
    public function deleteFile(string $filePath, ?string $disk = null): bool
    {
        return FileUploader::delete($filePath, $disk);
    }

    /**
     * Get the URL for a stored file.
     */
    public function fileUrl(string $filePath, ?string $disk = null): string
    {
        return FileUploader::url($filePath, $disk);
    }
}
