<?php

namespace Mhasnainjafri\RestApiKit\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploader
{
    /**
     * Upload a file to the specified or default disk.
     *
     * @return string The file path
     *
     * @throws \Exception
     */
    public static function upload(UploadedFile $file, ?string $path = null, ?string $disk = null): string
    {
        $disk = $disk ?? config('restify.file_upload_disk', 'local');
        $fileName = uniqid('', true).'.'.$file->getClientOriginalExtension();
        $filePath = $path ? "{$path}/{$fileName}" : $fileName;

        $stored = Storage::disk($disk)->put($filePath, file_get_contents($file->getRealPath()));

        if (! $stored) {
            throw new \Exception('File upload failed');
        }

        return $filePath;
    }

    /**
     * Delete a file.
     */
    public static function delete(string $filePath, ?string $disk = null): bool
    {
        $disk = $disk ?? config('restify.file_upload_disk', 'local');

        return Storage::disk($disk)->exists($filePath) && Storage::disk($disk)->delete($filePath);
    }

    /**
     * Get the URL for a stored file.
     */
    public static function url(string $filePath, ?string $disk = null): string
    {
        $disk = $disk ?? config('restify.file_upload_disk', 'local');

        return Storage::disk($disk)->url($filePath);
    }
}
