<?php

namespace Mhasnainjafri\RestApiKit\logger;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Mhasnainjafri\RestApiKit\logger\Contracts\ApiLoggerInterface;

class FileLogger extends AbstractLogger implements ApiLoggerInterface
{
    /**
     * file path to save the logs
     */
    protected $path;

    public function __construct()
    {
        parent::__construct();
        $this->path = storage_path('logs/apilogs');
    }

    /**
     * read files from log directory
     *
     * @return array
     */
    public function getLogs()
    {
        // Check if the directory exists
        if (File::isDirectory($this->path)) {
            // Scan the directory
            $files = glob($this->path.'/*.log');

            $contentCollection = collect();

            // Loop through each log file
            foreach ($files as $file) {
                if (! File::isDirectory($file)) {
                    // Read serialized content from file
                    $serializedData = file_get_contents($file);

                    // Trim any extra whitespace or characters
                    $serializedData = trim($serializedData);

                    // Unserialize data into PHP array/object
                    $logData = unserialize($serializedData);

                    if ($logData !== false) {
                        // Add log data to collection
                        $contentCollection->push($logData);
                    } else {
                        // Handle unserialize error if necessary
                        \Log::error("Failed to unserialize log data from file: $file");
                    }
                }
            }

            // Sort logs by created_at in descending order
            return $contentCollection->sortByDesc('created_at')->values()->all();
        } else {
            return [];
        }
    }

    /**
     * write logs to file
     *
     *
     * @return void
     */
    public function saveLogs(Request $request, Response|JsonResponse|RedirectResponse $response)
    {
        $data = $this->logData($request, $response);

        $filename = $this->getLogFilename();

        $contents = serialize($data);

        if (! File::isDirectory($this->path)) {
            File::makeDirectory($this->path, 0777, true, true);
        }

        File::append(($this->path.DIRECTORY_SEPARATOR.$filename), $contents.PHP_EOL);

    }

    /**
     * get log file if defined in constants
     *
     * @return string
     */
    public function getLogFilename()
    {
        // Default filename pattern
        $filename = 'apilogger-'.date('d-m-Y').'.log';

        // Check for the presence of [uuid] and replace it with a unique identifier if present
        if (strpos($filename, '[uuid]') !== false) {
            $filename = str_replace('[uuid]', uniqid(), $filename);
        } else {
            // Append unique identifier before the file extension
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $filename = substr($filename, 0, -(strlen($extension) + 1)).'-'.uniqid().".$extension";
        }

        return $filename;
    }

    /**
     * delete all api log  files
     *
     * @return void
     */
    public function deleteLogs()
    {
        if (is_dir($this->path)) {
            File::deleteDirectory($this->path);
        }

    }
}
