<?php

namespace Mhasnainjafri\RestApiKit\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class APIHelper
{
    /**
     * Format the response.
     *
     * @param  array  $data
     * @param  string  $msg
     * @param  int  $statusCode
     */
    public static function formatResponse($data, $msg, $statusCode, $meta = []): JsonResponse
    {

        $response = response()->json([
            'msg' => $msg,
            'data' => $data,
            'statusCode' => $statusCode,
            'meta' => $meta,
        ], $statusCode);

        self::saveLogs(request(), $response);

        return $response;
    }

    public static function saveLogs(Request $request, Response|JsonResponse|RedirectResponse $response)
    {
      
    }
}
