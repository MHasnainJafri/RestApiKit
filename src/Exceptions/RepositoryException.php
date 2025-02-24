<?php
namespace Mhasnainjafri\RestApiKit\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RepositoryException extends Exception
{
    protected array $errors = [];
    protected string $errorCode = 'REPOSITORY_ERROR';

    public function __construct(
        string $message = 'An error occurred in the repository',
        int $code = 500,
        array $errors = [],
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public static function validationFailed(array $errors): self
    {
        return new static(
            'Validation failed',
            422,
            $errors
        );
    }

    public static function notFound(string $message = 'Resource not found'): self
    {
        return new static($message, 404);
    }

    public static function unauthorized(string $message = 'Unauthorized access'): self
    {
        return new static($message, 403);
    }

    public static function queryError(Exception $exception): self
    {
        return new static(
            'Database query error',
            500,
            [],
            $exception
        );
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->message,
                'errors' => $this->errors,
            ]
        ], $this->code);
    }

    public function report(): void
    {
        Log::error("Repository Exception: {$this->message}", [
            'code' => $this->code,
            'errors' => $this->errors,
            'trace' => $this->getTraceAsString(),
        ]);
    }
}