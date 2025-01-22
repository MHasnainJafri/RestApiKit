<?php

namespace Mhasnainjafri\RestApiKit\Http\Responses;

use Illuminate\Pagination\LengthAwarePaginator;

class CustomPaginator
{
    public static function paginate(LengthAwarePaginator $paginator, ?string $message = null): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }
}
