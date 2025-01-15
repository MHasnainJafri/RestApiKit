<?php

namespace Mhasnainjafri\RestApiKit;

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
}
