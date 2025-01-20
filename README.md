## Introduction

The `mhasnainjafri/restapikit` package is a Laravel-based toolkit designed to simplify the development of RESTful APIs. It provides a uniform structure for handling responses, exceptions, file uploads, authentication, and more. This package is ideal for developers looking to streamline API development while adhering to best practices.

---

## Features

- **Uniform API Responses**: Standardized success, error, and paginated responses.
- **Exception Handling**: Beautifully formatted error responses for various exception types.
- **File Management**: Easy file upload, deletion, and URL generation.
- **Built-in Authentication**: Includes login, registration, password reset (via email or OTP), and email verification.
- **Caching**: Simplified caching for API responses.
- **Customizable HTTP Status Codes**: Predefined constants for common HTTP status codes.

---

## Installation

You can install the package via Composer:

```bash
composer require mhasnainjafri/restapikit
```

---

## Usage

### Extending the `RestController`

To use the package, extend your controller from `Mhasnainjafri\RestApiKit\Http\Controllers\RestController`. This provides access to the package's built-in methods for handling responses, exceptions, and more.

```php
use Mhasnainjafri\RestApiKit\Http\Controllers\RestController;

class BusinessController extends RestController
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $data = $this->service->create($validated);

        return $this->response($data, __('messages.store', ['model' => 'Business']), API::CREATED);
    }
}
```

---

## Functionalities

### 1. **Uniform API Responses**

The package provides a consistent structure for API responses. For example:

#### Success Response
```php
return $this->response($data, 'Data retrieved successfully.', API::SUCCESS);
```

#### Paginated Response
If `$data` is paginated, the response will automatically include pagination metadata:
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "id": 1,
                "name": "API Toolkit"
            }
        ],
        "meta": {
            "current_page": 1,
            "total_pages": 1,
            "per_page": 10,
            "total": 2
        }
    },
    "message": "Data retrieved successfully."
}
```

Alternatively, you can explicitly return a paginated response:
```php
return $this->response()
    ->paginate($users, 'users')
    ->message('Users retrieved successfully.')
    ->toResponse();
```

---

### 2. **Exception Handling**

The package simplifies exception handling by providing pre-defined methods for common scenarios:

#### General Exception
```php
catch (Exception $exception) {
    return $this->exception($exception, "Something went wrong", API::INTERNAL_SERVER_ERROR);
}
```

#### Validation Exception
```php
if ($exception instanceof ValidationException) {
    return $this->response()
        ->errors($exception->errors())
        ->status(API::UNPROCESSABLE_ENTITY);
}
```

#### Unauthorized Access
```php
if ($exception instanceof UnauthorizedException) {
    return $this->response()
        ->message('Unauthorized access')
        ->status(API::FORBIDDEN);
}
```

#### Server Error
```php
return $this->response()
    ->message('Server error')
    ->status(API::INTERNAL_SERVER_ERROR);
```

---

### 3. **File Management**

The package includes utilities for file uploads, deletions, and URL generation.

#### Upload a File
```php
$filePath = $this->upload($file, 'uploads/documents', 'local');
```

#### Delete a File
```php
$this->deleteFile($filePath, 'local');
```

#### Generate File URL
```php
$url = $this->fileUrl($filePath, 'local');
```

---

### 4. **Caching API Responses**

The `cacheResponse` method allows you to cache API responses for a specified duration.

#### Example:
```php
public function index()
{
    return $this->cacheResponse('users.index', function () {
        return User::all();
    }, 30); // Cache for 30 minutes
}
```

---

### 5. **Built-in Authentication**

The package provides pre-defined routes and methods for common authentication tasks:

#### Available Routes:
```php
$routes = [
    'login' => ['POST', 'login', 'login'],
    'register' => ['POST', 'register', 'register'],
    'forgotPassword' => ['POST', 'restify/forgotPassword', 'forgotPassword'],
    'resetPassword' => ['POST', 'restify/resetPassword', 'resetPassword'],
    'verifyEmail' => ['POST', 'restify/verify/{id}/{emailHash}', 'verifyEmail'],
    'sendOtp' => ['POST', 'restify/verify/{id}/{emailHash}', 'sendOtp'],
    'verifyOtp' => ['POST', 'restify/verify/{id}/{emailHash}', 'verifyOtp'],
    'changePassword' => ['POST', 'restify/verify/{id}/{emailHash}', 'changePassword'],
];
```

---

### 6. **HTTP Status Codes**

The package includes a predefined `API` class with constants for common HTTP status codes:

```php
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
```

---

### 7. **Custom Macros**

You can define custom macros for reusable functionality. For example:

#### Example Macro:
```php
app(ActionMacroManager::class)->macro('greetUser', function ($name) {
    return "Hello, {$name}!";
});
```

---

## Testing

To run the package's tests, use the following command:

```bash
composer test
```

---

## Contributing

Contributions are welcome! Please see the [CONTRIBUTING](CONTRIBUTING.md) file for details.

---

## Security

If you discover any security-related issues, please email `mhasnainjafri51214@gmail.com` instead of using the issue tracker.

---

## License

This package is open-sourced software licensed under the **MIT License**. See the [LICENSE](LICENSE.md) file for more details.

---

## Credits

- **Author**: [Muhammad Hasnain](https://github.com/mhasnainjafri)
- **Contributors**: [All Contributors](../../contributors)

---

This package boilerplate was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).