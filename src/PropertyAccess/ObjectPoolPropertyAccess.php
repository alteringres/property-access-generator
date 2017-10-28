<?php

namespace Ingres\PropertyAccess;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;


/**
 * Class ObjectPoolPropertyAccess
 * @package Ingres\Symfony\PropertyAccess
 */
class ObjectPoolPropertyAccess implements PropertyAccessorInterface
{
    /**
     * @var array|PropertyAccessorInterface[]
     */
    protected $classPropertyAccessors = [];

    /**
     * ObjectPoolPropertyAccess constructor.
     */
    public function __construct()
    {
        $this->classPropertyAccessors = [
            #__AUTO_GENERATE_CLASS_INSTANCES__
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(&$objectOrArray, $propertyPath, $value)
    {
        $class = get_class($objectOrArray);
        return $this->classPropertyAccessors[$class]->setValue($objectOrArray, $propertyPath, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($objectOrArray, $propertyPath)
    {
        $class = get_class($objectOrArray);
        return $this->classPropertyAccessors[$class]->getValue($objectOrArray, $propertyPath);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable($objectOrArray, $propertyPath)
    {
        $class = get_class($objectOrArray);
        return $this->classPropertyAccessors[$class]->isWritable($objectOrArray, $propertyPath);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable($objectOrArray, $propertyPath)
    {
        $class = get_class($objectOrArray);
        return $this->classPropertyAccessors[$class]->isReadable($objectOrArray, $propertyPath);
    }
}