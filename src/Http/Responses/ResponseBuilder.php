<?php

namespace Mhasnainjafri\RestApiKit\Http\Responses;

use Illuminate\Http\JsonResponse;

class ResponseBuilder
{
    protected $data = [];

    protected $meta = [];

    protected $headers = [];

    protected $status;

    protected $message = null;

    protected $debug = [];

    protected $errors = [];

    public function __construct($data = null, int $status = 200)
    {
        $this->data = $data;
        $this->status = $status;
    }

    public function data($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function setMeta(string $key, $value): self
    {
        $this->meta[$key] = $value;

        return $this;
    }

    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function line($lineNumber): self
    {
        $this->debug['line'] = $lineNumber;

        return $this;
    }

    public function file($filePath): self
    {
        $this->debug['file'] = $filePath;

        return $this;
    }

    public function stack($stackTrace): self
    {
        $this->debug['stack'] = $stackTrace;

        return $this;
    }

    public function errors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    public function addError(string $error): self
    {
        $this->errors[] = $error;

        return $this;
    }

    public function toResponse(): JsonResponse
    {
        $response = [
            'success' => empty($this->errors),
            'data' => $this->data,
            'message' => $this->message,
        ];

        if (! empty($this->errors)) {
            $response['errors'] = $this->errors;
        }

        if (! empty($this->meta)) {
            $response['meta'] = $this->meta;
        }

        if (! empty($this->debug)) {
            $response['debug'] = $this->debug;
        }

        return response()->json($response, $this->status, $this->headers);
    }

    public function paginate($paginator, string $resourceKey = 'data')
    {
        $this->data = CustomPaginator::paginate($paginator, $this->message);
    }
}
