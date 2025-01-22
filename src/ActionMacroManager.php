<?php

namespace Mhasnainjafri\RestApiKit;

use Illuminate\Support\Traits\Macroable;

class ActionMacroManager
{
    use Macroable {
        __call as macroCall;
    }

    public function execute($name, ...$parameters)
    {
        if (! static::hasMacro($name)) {
            throw new \BadMethodCallException("Macro {$name} is not defined.");
        }

        return $this->macroCall($name, $parameters);
    }
}
