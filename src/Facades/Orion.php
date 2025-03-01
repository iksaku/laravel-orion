<?php

namespace Orion\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method \Illuminate\Routing\PendingResourceRegistration resource(string $name, string $controller, array $options = [])
 * @method \Illuminate\Routing\PendingResourceRegistration hasOneResource(string $resource, string $relation, string $controller, array $options = [])
 * @method \Illuminate\Routing\PendingResourceRegistration belongsToResource(string $resource, string $relation, string $controller, array $options = [])
 * @method \Illuminate\Routing\PendingResourceRegistration hasManyResource(string $resource, string $relation, string $controller, array $options = [])
 * @method \Illuminate\Routing\PendingResourceRegistration belongsToManyResource(string $resource, string $relation, string $controller, array $options = [])
 * @method \Illuminate\Routing\PendingResourceRegistration hasOneThroughResource(string $resource, string $relation, string $controller, array $options = [])
 * @method \Illuminate\Routing\PendingResourceRegistration hasManyThroughResource(string $resource, string $relation, string $controller, array $options = [])
 * @method \Illuminate\Routing\PendingResourceRegistration morphOneResource(string $resource, string $relation, string $controller, array $options = [])
 * @method \Illuminate\Routing\PendingResourceRegistration morphManyResource(string $resource, string $relation, string $controller, array $options = [])
 * @method \Illuminate\Routing\PendingResourceRegistration morphToResource(string $resource, string $relation, string $controller, array $options = [])
 * @method \Illuminate\Routing\PendingResourceRegistration morphToManyResource(string $resource, string $relation, string $controller, array $options = [])
 * @method \Illuminate\Routing\PendingResourceRegistration morphByManyResource(string $resource, string $relation, string $controller, array $options = [])
 *
 * @see \Orion\Orion
 */
class Orion extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'orion';
    }
}
