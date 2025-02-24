<?php

namespace Mhasnainjafri\RestApiKit\Repositories;

use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Support\{Facades\Cache, Facades\Gate, Str};
use Illuminate\Http\{Request, Resources\Json\JsonResource};
use Illuminate\Validation\ValidationException;
use App\Exceptions\RepositoryException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mhasnainjafri\RestApiKit\API;
use ReflectionClass;

abstract class BaseRepository
{
    protected static string $model;
    protected static array $searchable = [];
    protected static array $sortable = [];
    protected static array $defaultRelations = [];
    protected static array $allowedRelations = [];
    protected static string $resourceClass = JsonResource::class;
    protected static int $defaultPerPage = 15;
    protected static bool $forcePaginate = false;
    protected static int $cacheTtl = 60;
    protected static string|null $cacheTag = null;
    protected static bool $useSoftDeletes = false;
    protected static string $mediaPath='uploads';
    protected static string $mediaDisk;

    public static function boot()
    {
        static::$mediaDisk = config('filesystems.default', 'public');
    }
    
    protected static function getModelInstance(): Model
    {
        return app(static::$model);
    }

    protected static function checkSoftDeletes(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive(static::$model));
    }

    // Core CRUD Operations
    public static function index(Request $request)
    {
        $query = static::getModelInstance()->query();
        $query = static::beforeIndex($query, $request);
        $query = static::applyCommonConditions($query, $request);
        $query = static::afterIndex($query, $request);

        static::authorize('view', static::getModelInstance());

        return static::formatResponse(
            static::executeQuery($query, $request),
            'Data retrieved successfully',
            API::SUCCESS,
            'index'
        );
    }

    public static function show(Request $request, $id)
    {
        $query = static::getModelInstance()->query();
        $query = static::beforeShow($query, $request);
        $query = static::applyCommonConditions($query, $request);
        $query = static::afterShow($query, $request);

        $model = $query->findOrFail($id);
        static::authorize('view', $model);

        return static::formatResponse($model, 'Resource retrieved successfully', API::SUCCESS, 'show');
    }

    public static function store(Request $request)
    {
        static::authorize('create', static::$model);

        $validated = static::validate($request, 'store');
        $model = static::getModelInstance()->newInstance();

        $model = static::beforeSave($model, $validated, 'store');
        $model->fill($validated)->save();
        $model = static::afterSave($model, $validated, 'store');

        static::clearCache();
        return static::formatResponse($model, 'Resource created', API::CREATED, 'store');
    }

    public static function update(Request $request, $id)
    {
        $model = static::$model::findOrFail($id);
        static::authorize('update', $model);

        $validated = static::validate($request, 'update');

        $model = static::beforeSave($model, $validated, 'update');
        $model->fill($validated)->save();
        $model = static::afterSave($model, $validated, 'update');

        static::clearCache();
        return static::formatResponse($model, 'Resource updated', API::SUCCESS, 'update');
    }

    public static function destroy($id)
    {
        $model = static::$model::when(static::checkSoftDeletes(), function ($query) {
            $query->withTrashed();
        })->findOrFail($id);

        static::authorize('delete', $model);

        if (static::checkSoftDeletes() && !$model->trashed()) {
            $model->delete();
            $message = 'Resource soft deleted';
        } else {
            $model->forceDelete();
            $message = 'Resource permanently deleted';
        }

        static::clearCache();
        return static::formatResponse(null, $message, API::SUCCESS, 'destroy');
    }
    /**
     * Execute the query and return the results.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @return mixed Paginated or non-paginated results
     */
    protected static function executeQuery($query, Request $request)
{
    // Determine if pagination is requested (default: true)
    $paginate = filter_var($request->query('paginate', true), FILTER_VALIDATE_BOOLEAN);

    // Get the number of items per page (default: from repository configuration)
    $perPage = $request->query('per_page', static::$defaultPerPage);

    // Generate cache key if caching is enabled
    $key = static::generateCacheKey($request, static::$cacheTag);

    // Check for cached results
    if (static::$cacheTag && $key && Cache::has($key)) {
        return Cache::get($key);
    }

    // Execute the query
    $result = $paginate ? $query->paginate($perPage) : $query->get();

    // Cache the result if caching is enabled
    if (static::$cacheTag && $key) {
        $cache = Cache::getFacadeRoot();
    
        // Check if the cache driver supports tags
        if (method_exists($cache->store()->getStore(), 'tags')) {
            // Use tags for cache storage (supported by Redis, Memcached, etc.)
            Cache::tags(static::$cacheTag)->put($key, $result, static::$cacheTtl);
        } else {
            // Fallback for drivers that don't support tags (e.g., file, database)
            Cache::put($key, $result, static::$cacheTtl);
        }
    }

    return $result;
}



    // Query Building
    protected static function applyCommonConditions(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->has('search'), fn($q) => static::applySearch($q, $request))
            ->when($request->has('filter'), fn($q) => static::applyFilters($q, $request))
            ->when($request->has('sort'), fn($q) => static::applySorting($q, $request))
            ->when($request->has('with'), fn($q) => static::applyRelations($q, $request));
    }
    protected static function applySearch(Builder $query, Request $request): Builder
    {
        $searchTerm = $request->input('search');
        return $query->where(function ($q) use ($searchTerm) {
            foreach (static::$searchable as $field) {
                $q->orWhere($field, 'LIKE', "%{$searchTerm}%");
            }
        });
    }

    protected static function applyFilters(Builder $query, Request $request): Builder
    {
        $filters = json_decode($request->input('filter'), true) ?? [];
    
        foreach ($filters as $field => $condition) {
            foreach ($condition as $operator => $value) {
                // Check if the operator is a scope
                if (method_exists(static::$model, 'scope' . ucfirst($operator))) {
                    $query->$operator($value);
                } else {
                    $query->where(
                        $field,
                        static::mapOperator($operator),
                        static::formatFilterValue($operator, $value)
                    );
                }
            }
        }
    
        return $query;
    }

    protected static function applySorting(Builder $query, Request $request): Builder
    {
        $sortFields = explode(',', $request->input('sort'));
        $directions = explode(',', $request->input('direction', 'asc'));

        foreach ($sortFields as $index => $field) {
            if (in_array($field, static::$sortable)) {
                $direction = $directions[$index] ?? 'asc';
                $query->orderBy($field, $direction);
            }
        }
        return $query;
    }

    protected static function applyRelations(Builder $query, Request $request): Builder
    {
        $requestedRelations = array_intersect(
            explode(',', $request->input('with')),
            static::$allowedRelations
        );
        return $query->with(array_merge(
            static::$defaultRelations,
            $requestedRelations
        ));
    }



    // Hooks
    protected static function beforeIndex(Builder $query, Request $request): Builder
    {
        return $query;
    }
    protected static function afterIndex(Builder $query, Request $request): Builder
    {
        return $query;
    }
    protected static function beforeShow(Builder $query, Request $request): Builder
    {
        return $query;
    }
    protected static function afterShow(Builder $query, Request $request): Builder
    {
        return $query;
    }
    protected static function beforeSave(Model $model, array $data, string $operation): Model
    {
        return $model;
    }
    protected static function afterSave(Model $model, array $data, string $operation): Model
    {
        return $model;
    }

    // Validation
    protected static function validate(Request $request, string $operation): array
    {
        $rules = static::validationRules($operation);
        $messages = static::validationMessages();
        $attributes = static::validationAttributes();

        try {
            $validated = $request->validate($rules, $messages, $attributes);

            if (empty($validated)) {
                $validated= $request->all();
            }
            $validated=static::validateAndUploadFiles($request,$validated);
            return $validated;
        } catch (ValidationException $e) {
            throw RepositoryException::validationFailed($e->errors());
        }
    }
    
    protected static function validateAndUploadFiles(Request $request,$validatedData): array
    {
        foreach ($validatedData as $key => $value) {
            if ($request->hasFile($key) && $request->file($key)->isValid()) {
                $path = $request->file($key)->store(static::$mediaPath, static::$mediaDisk); // Change 'uploads' to your desired directory
                $validatedData[$key] = $path;
            }
        }
    
        return $validatedData;
    }
    

    public static function validationRules(string $operation): array
    {
        return [];
    }

    protected static function validationMessages(): array
    {
        return [];
    }

    protected static function validationAttributes(): array
    {
        return [];
    }

    protected static function formatResponse($data, string $message = '', int $code = 200, string $operation = null)
{
    if ($operation == 'index' && static::$resourceClass != JsonResource::class) {
        $data = static::$resourceClass::collection($data);
    } elseif ($operation == 'show' && static::$resourceClass != JsonResource::class) {
        $data = new static::$resourceClass($data);
    }

    // Check if the original data is paginated
    $originalData = $data->resource ?? null;

    if ($originalData instanceof LengthAwarePaginator) {
        return API::paginated($data, $message, $code);
    }

    return API::response($data, [
        'message' => $message,
        'timestamp' => now()->toISOString(),
        'links' => [
            'self' => request()->fullUrl(),
        ],
        'meta' => [
            'code' => $code,
            'status' => 'success',
        ],
    ], $code);
}

    protected static function mapOperator(string $operator): string
    {
        return match ($operator) {
            'gt' => '>',
            'gte' => '>=',
            'lt' => '<',
            'lte' => '<=',
            'neq' => '!=',
            'like' => 'LIKE',
            default => '='
        };
    }

    protected static function formatFilterValue(string $operator, $value)
    {
        return $operator === 'like' ? "%{$value}%" : $value;
    }
    protected static function clearCache(): void
{
    if (!static::$cacheTag) {
        return; // No cache tag defined, nothing to clear
    }

    $cache = Cache::getFacadeRoot();

    // Check if the cache driver supports tags
    if (method_exists($cache->store()->getStore(), 'tags')) {
        // Clear cache using tags (for drivers like Redis, Memcached)
        Cache::tags(static::$cacheTag)->flush();
    } else {
        // Fallback for drivers that don't support tags (e.g., file, database)
        // Clear the entire cache (less efficient but works for all drivers)
        Cache::flush();
    }
}

protected static function authorize(string $ability, mixed $arguments = null): void
{
    if (!Gate::has($ability)) {
        return ;
        // throw new \RuntimeException("Authorization ability '{$ability}' is not defined.");
    }

    Gate::authorize($ability, $arguments);
}
protected static function generateCacheKey(Request $request, string $tag): string
{
    // Combine all query parameters into a sorted array for consistency
    $queryParams = $request->query();
    ksort($queryParams); // Sort parameters alphabetically to ensure consistent ordering

    // Add user-specific context (if authenticated)
    $userContext = $request->user() ? $request->user()->id : 'guest';

    // Convert the query parameters into a query string
    $queryString = http_build_query($queryParams);

    // Combine the tag, user context, and query string to create a unique key
    $uniqueKey = "{$tag}_{$userContext}_" . md5($queryString);

    return $uniqueKey;
}
}
