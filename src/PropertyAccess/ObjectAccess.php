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
     * @var
     */
    protected $class;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * ObjectAccess constructor.
     * @param $class
     * @param $attributes
     */
    public function __construct($class, $attributes)
    {
        $this->class = $class;
        $this->attributes = $attributes;
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
}
