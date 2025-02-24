<?php

namespace Mhasnainjafri\RestApiKit\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class CustomHandler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof RepositoryException) {
            return $exception->render();
        }

        return parent::render($request, $exception);
    }
}