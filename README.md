## Introduction

The `mhasnainjafri/restapikit` package is a Laravel-based toolkit designed to simplify the development of RESTful APIs.
It provides a uniform structure for handling responses, exceptions, file uploads, authentication, and more. This package
is ideal for developers looking to streamline API development while adhering to best practices.

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

To use the package, extend your controller from `Mhasnainjafri\RestApiKit\Http\Controllers\RestController`. This
provides access to the package's built-in methods for handling responses, exceptions, and more.

```php
use Mhasnainjafri\RestApiKit\Http\Controllers\RestController;

class BusinessController extends RestController
{
  public function store(Request $request)
    {
     $data = $this->service->create($validated);
    return $this->response($data,"Record has been saved successfully", API::CREATED);
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


### 1. **Response Handling Without Controller Extension**
The package provides a standalone API response helper for developers who prefer not to extend the `RestController`:

```php
API::success($data, 'Data retrieved successfully');
API::error('An error occurred', API::INTERNAL_SERVER_ERROR);

// Additional response helpers:
API::validationError($errors);
API::notFound('User not found');
API::cachedResponse($resource, $cacheKey);
API::paginatedCachedResponse($resource, $pageNumber);
API::clearCacheKey($cacheKey);
```

#### **Success Response**
```php
return API::response($data, 'Data retrieved successfully.', API::SUCCESS);
```

#### **Paginated Response**
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

Alternatively, explicitly return a paginated response:

```php
return API::response()
->paginate($users, 'users')
->message('Users retrieved successfully.')
->toResponse();
```

### 2. **Exception Handling**
Simplified exception handling with pre-defined methods for common scenarios:

#### **General Exception**
```php
catch (Exception $exception) {
return API::exception($exception, "Something went wrong", API::INTERNAL_SERVER_ERROR);
}
```

#### **Validation Exception**
```php
if ($exception instanceof ValidationException) {
return API::validationError($exception->errors());
}
```

#### **Unauthorized Access**
```php
if ($exception instanceof UnauthorizedException) {
return API::error('Unauthorized access', API::FORBIDDEN);
}
```

#### **Server Error**
```php
return API::error('Server error', API::INTERNAL_SERVER_ERROR);
```

### 3. **File Management**
Built-in utilities for managing file uploads, deletions, and generating file URLs.

#### **Upload a File**
```php
$filePath = API::upload($file, 'uploads/documents', 'local');
```

#### **Delete a File**
```php
API::deleteFile($filePath, 'local');
```

#### **Generate File URL**
```php
$url = API::fileUrl($filePath, 'local');
```

### 4. **Caching API Responses**
Effortlessly cache API responses using the `cacheResponse` method or the `API` facade.

#### Example with `$this`:
```php
public function index()
{
return API::cacheResponse('users.index', function () {
return User::all();
}, 30); // Cache for 30 minutes
}
```

#### Example with `API` Facade:
```php
return API::cachedResponse(User::all(), 'users.index');
```

#### Clear Cache:
```php
API::clearCacheKey('users.index');
```

---

### Notes
By offering both `RestController` methods and the `API` facade, this package empowers developers with flexible options
to build robust APIs. Whether you prefer extending the controller or using standalone helpers, the toolkit adapts to
your workflow.

---

### **5. Built-in Authentication**

The package includes pre-defined routes and methods to simplify common authentication tasks, ensuring seamless
integration into your application.

#### **Available Routes**
To enable authentication routes, include the following line in your `api.php`:

```php
Route::restifyAuth();
```

##### **Customizing Routes**
You can specify only the required routes:

```php
Route::restifyAuth(['login', 'register']);
```

##### **Supported Authentication Routes**
The following routes are available by default:

- **`login`**
- **`register`**
- **`forgotPassword`**
- **`resetPassword`**
- **`verifyEmail`**
- **`sendOtp`**
- **`verifyOtp`**
- **`changePassword`**

---

#### **Postman Collection**
A Postman collection is available to test the authentication endpoints. [Download the Postman Collection here](#).

---

#### **Authentication Methods**
The package supports both **Laravel Passport** and **Laravel Sanctum** for authentication. To set up the desired method,
run the following command:

```bash
php artisan RestApiKit:setup-auth
```

---

#### **Publishing Authentication Controllers**
You can customize authentication controllers by publishing them to your project. Run the command below:

```bash
php artisan vendor:publish --tag=restify-AuthControllers
```

This will generate authentication controllers in the following directory:

```
app/Http/Controllers/RestApi/Auth
```

After publishing the controllers, update the `config/restify.php` file to define the correct namespace for your
authentication controllers.

---

### 6. **HTTP Status Codes**

The package includes a predefined `API` class with constants for common HTTP status codes:

## Constants

APITOOLKIT provides various HTTP status codes as constants for convenience:

- `API::SUCCESS`: 200
- `API::CREATED`: 201
- `API::NO_CONTENT`: 204
- `API::BAD_REQUEST`: 400
- `API::UNAUTHORIZED`: 401
- `API::FORBIDDEN`: 403
- `API::NOT_FOUND`: 404
- `API::METHOD_NOT_ALLOWED`: 405
- `API::UNPROCESSABLE_ENTITY`: 422
- `API::INTERNAL_SERVER_ERROR`: 500
- `API::NOT_IMPLEMENTED`: 501
- `API::BAD_GATEWAY`: 502
- `API::SERVICE_UNAVAILABLE`: 503


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

## **Todo list**


1. Logger



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

If you discover any security-related issues, please email `mhasnainjafri51214@gmail.com` instead of using the issue
tracker.

---

## License

This package is open-sourced software licensed under the **MIT License**. See the [LICENSE](LICENSE.md) file for more
details.

---

## Credits

- **Author**: [Muhammad Hasnain](https://github.com/mhasnainjafri)
- **Contributors**: [All Contributors](../../contributors)

---

This package boilerplate was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
