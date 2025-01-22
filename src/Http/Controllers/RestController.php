<?php

namespace Mhasnainjafri\RestApiKit\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Mhasnainjafri\RestApiKit\API;
use Mhasnainjafri\RestApiKit\Helpers\FileUploader;

class RestController extends Controller
{
    /**
     * Generates a JSON response with the given data, message, and status code.
     *
     * @param  mixed  $data  The data to be included in the response.
     * @param  string|array|null  $message  The message to be included in the response.
     * @param  int  $status  The HTTP status code for the response.
     */
    protected function response($data = null, string|array|null $message = null, int $status = 200): JsonResponse
    {
        return API::response($data, $message, $status);
    }

    /**
     * Handles and generates a detailed JSON response for an exception.
     *
     * @param  \Exception  $exception  The exception to be processed.
     * @param  string  $message  The custom message for the exception response.
     * @param  int  $status  The HTTP status code for the response.
     */

    /**
     * Handles and generates a detailed JSON response for an exception.
     *
     * @param  \Exception  $exception  The exception to be processed.
     * @param  string  $message  The custom message for the exception response.
     * @param  int  $status  The HTTP status code for the response.
     */
    protected function exception($exception, $message, $status): JsonResponse
    {
        return API::exception($exception, $message, $status);
    }

    /**
     * Generates a JSON response with the given validation errors and status code.
     *
     * @param  array  $errors  The validation errors.
     * @param  int  $status  The HTTP status code for the response.
     */
    protected function errors(array $errors, int $status = 400): JsonResponse
    {
        return API::errors($errors, $status);
    }

    /**
     * Caches the response of a given callback for a specified period.
     *
     * @param  string  $key  The cache key to identify the response.
     * @param  callable  $callback  The callback function to generate the response data.
     * @param  int|null  $minutes  The number of minutes to cache the response. Defaults to the configuration value if not provided.
     */
    protected function cacheResponse(string $key, callable $callback, ?int $minutes = null): JsonResponse
    {
        return API::cacheResponse($key, $callback, $minutes);
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
     * Deletes a file using FileUploader.
     *
     * @param  string  $filePath  The file path to delete.
     * @param  string|null  $disk  The disk to use when deleting the file. Defaults to the default disk if not provided.
     * @return bool Whether the file was successfully deleted.
     */
    public function deleteFile(string $filePath, ?string $disk = null): bool
    {
        return FileUploader::delete($filePath, $disk);
    }

    /**
     * Get the URL for a stored file.
     *
     * @param  string  $filePath  The file path to generate a URL for.
     * @param  string|null  $disk  The disk to use when generating the URL. Defaults to the default disk if not provided.
     * @return string The URL for the stored file.
     */
    public function fileUrl(string $filePath, ?string $disk = null): string
    {
        return FileUploader::url($filePath, $disk);
    }

    /**
     * Clear a cache key.
     *
     * @param  string  $cacheKey  The cache key to clear.
     * @return bool Whether the cache key was successfully cleared.
     */
    private function clearCacheKey(string $cacheKey): bool
    {
        return Cache::forget($cacheKey);
    }

    /**
     * Return a validation error response
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function validationError(array $errors)
    {
        return API::validationError($errors);
    }
}
