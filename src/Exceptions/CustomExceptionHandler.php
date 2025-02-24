<?php

namespace Mhasnainjafri\RestApiKit\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class CustomExceptionHandler extends Exception
{
    /**
     * Handle an exception and return the appropriate response.
     *
     * @return mixed
     */
    public function handle(Throwable $exception, Request $request)
    {
        if ($exception instanceof NotFoundHttpException) {
            return $this->handleNotFound($request);
        }

        if ($exception instanceof AuthenticationException) {
            return $this->handleUnauthorized($request);
        }

        // Fallback for all other exceptions
        return $this->handleGeneric($exception, $request);
    }

    /**
     * Handle a NotFoundHttpException.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function handleNotFound(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
            ], 404);
        }

        return redirect('/404');
    }

    /**
     * Handle an AuthenticationException.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function handleUnauthorized(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        return redirect('/login');
    }

    /**
     * Handle a generic exception.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Contracts\View\View
     */
    protected function handleGeneric(Throwable $exception, Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'An unexpected error occurred.',
                'trace' => config('app.debug') ? $exception->getTrace() : null,
            ], 500);
        }

        return view('errors.500', ['message' => $exception->getMessage()]);
    }
}
