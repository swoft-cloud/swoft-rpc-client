<?php

namespace Swoft\Rpc\Client\Bean\Wrapper;

use Swoft\Bean\Annotation\Inject;
use Swoft\Bean\Annotation\Value;
use Swoft\Rpc\Client\Bean\Annotation\Reference;

/**
 * The bean wrapper
 */
class BeanWrapper extends \Swoft\Bean\Wrapper\BeanWrapper
{
    /**
     * @var array
     */
    protected $propertyAnnotations
        = [
            Inject::class,
            Value::class,
            Reference::class,
        ];

    /**
     * @param array $annotations
     *
     * @return bool
     */
    public function isParsePropertyAnnotations(array $annotations): bool
    {
        $parent = parent::isParsePropertyAnnotations($annotations);

        return $parent || isset($annotations[Reference::class]);
    }
}