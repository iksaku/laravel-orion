<?php

namespace Orion\Http\Controllers;

use Orion\Concerns\BuildsRelationsQuery;
use Orion\Concerns\HandlesRelationManyToManyOperations;
use Orion\Concerns\HandlesRelationOneToManyOperations;
use Orion\Concerns\HandlesRelationStandardOperations;

class RelationController extends BaseController
{
    use BuildsRelationsQuery, HandlesRelationStandardOperations, HandlesRelationOneToManyOperations, HandlesRelationManyToManyOperations;

    /**
     * @var string|null $relation
     */
    protected static $relation = null;

    /**
     * @var string|null $relation
     */
    protected static $associatingRelation = null;

    /**
     * The list of pivot fields that can be set upon relation resource creation or update.
     *
     * @var bool
     */
    protected $pivotFillable = [];

    /**
     * The list of pivot json fields that needs to be casted to array.
     *
     * @var array
     */
    protected $pivotJson = [];

    /**
     * Retrieves model related to resource.
     *
     * @return string
     */
    protected function getResourceModel()
    {
        return get_class((new static::$model)->{static::$relation}()->getRelated());
    }
}
