<?php

namespace Swoft\Rpc\Client\Bean\Wrapper;

use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Value;
use Swoft\Rpc\Client\Bean\Annotation\Reference;

/**
 * Task wrapper
 */
class TaskWrapper extends \Swoft\Task\Bean\Wrapper\TaskWrapper
{
    /**
     * @var array
     */
    protected $propertyAnnotations = [
        Inject::class,
        Value::class,
        Reference::class
    ];

    /**
     * @param array $annotations
     *
     * @return bool
     */
    public function isParsePropertyAnnotations(array $annotations): bool
    {
        return parent::isParsePropertyAnnotations($annotations) || isset($annotations[Reference::class]);
    }
}