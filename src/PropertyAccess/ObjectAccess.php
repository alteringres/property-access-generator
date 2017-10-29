<?php

namespace Ingres\PropertyAccess;

/**
 * Class ObjectAccess
 *
 * Data Object, used to pass class instance and it's attributes to ObjectPoolAccessBuilder
 *
 * @package Ingres\PropertyAccess
 */
class ObjectAccess
{
    /**
     * Defines operation set type for property access
     */
    const OPERATION_SET = 'operation.set';

    /**
     * Defines operation get type for property access
     */
    const OPERATION_GET = 'operation.get';

    /**
     * @var
     */
    protected $class;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var array
     */
    protected $classAliases;

    /**
     * ObjectAccess constructor.
     * @param $class
     * @param $attributes
     * @param array $classAliases
     */
    public function __construct(
        $class,
        $attributes,
        array $classAliases
    ) {
        $this->class = $class;
        $this->attributes = $attributes;
        $classAliases[] = get_class($class);
        $this->classAliases = $classAliases;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array
     */
    public function getClassAliases(): array
    {
        return $this->classAliases;
    }
}
