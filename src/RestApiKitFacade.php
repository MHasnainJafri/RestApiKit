<?php

namespace Mhasnainjafri\RestApiKit;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mhasnainjafri\RestApiKit\Skeleton\SkeletonClass
 */
class RestApiKitFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'restapikit';
    }
}
