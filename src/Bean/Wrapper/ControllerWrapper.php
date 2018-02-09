<?php

namespace Swoft\Rpc\Client\Bean\Wrapper;

use Swoft\Bean\Wrapper\AbstractWrapper;
use Swoft\Rpc\Client\Bean\Annotation\Reference;

/**
 * The wrapper of controller
 */
class ControllerWrapper extends AbstractWrapper
{
    /**
     * 类注解
     *
     * @var array
     */
    protected $classAnnotations = [];

    /**
     * 属性注解
     *
     * @var array
     */
    protected $propertyAnnotations = [
        Reference::class
    ];

    /**
     * 方法注解
     *
     * @var array
     */
    protected $methodAnnotations = [
    ];

    /**
     * 是否解析类注解
     *
     * @param array $annotations
     *
     * @return bool
     */
    public function isParseClassAnnotations(array $annotations): bool
    {
        return false;
    }

    /**
     * 是否解析属性注解
     *
     * @param array $annotations
     *
     * @return bool
     */
    public function isParsePropertyAnnotations(array $annotations): bool
    {
        return isset($annotations[Reference::class]);
    }

    /**
     * 是否解析方法注解
     *
     * @param array $annotations
     *
     * @return bool
     */
    public function isParseMethodAnnotations(array $annotations): bool
    {
        return false;
    }
}