<?php

namespace Ingres\PropertyAccess;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;


/**
 * Class ObjectPoolPropertyAccess
 * @package Ingres\Symfony\PropertyAccess
 */
class ObjectPoolPropertyAccess implements ObjectPoolPropertyAccessInterface
{
    /**
     * @var array|GeneratedPropertyAccessorInterface[]
     */
    protected $classPropertyAccessors = [];

    /** @var ObjectPoolPropertyAccess */
    protected static $instance = null;

    /**
     * Since nothing can change during runtime, this must be a singleton
     *
     * @return ObjectPoolPropertyAccess
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * ObjectPoolPropertyAccess constructor.
     */
    protected function __construct()
    {
#__AUTO_GENERATE_OBJECT_ASSIGNMENT__

        $this->classPropertyAccessors = [
#__AUTO_GENERATE_CLASS_PROPERTY_ACCESSORS___
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

    /**
     * @param $object
     * @return bool
     */
    public function hasAccessTo($object)
    {
        $class = get_class($object);
        return isset($this->classPropertyAccessors[$class]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasAccessToOperation($object, $property, $operation)
    {
        if (is_object($object)) {
            $class = get_class($object);
            if (isset($this->classPropertyAccessors[$class])) {
                return $this->classPropertyAccessors[$class]->hasAccessToOperation(
                    $property,
                    $operation
                );
            }
        }

        return false;
    }
}