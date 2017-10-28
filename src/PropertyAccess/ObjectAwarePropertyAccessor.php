<?php

namespace Ingres\PropertyAccess;


use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Class ObjectAwarePropertyAccessor
 * @package Ingres\Symfony\PropertyAccess
 */
#remove_all_before_this
class ObjectAwarePropertyAccessor#__CALL_CONSTRUCTOR__
    implements
        PropertyAccessorInterface
{
    const READ_FUNCTION_PREFIX = 'get_';

    const WRITE_FUNCTION_PREFIX = 'set_';

    const WRITE_COLLECTION_FUNCTION_PRFIX = 'set_c_';

    protected $isWritable = [
#__AUTO_GENERATED_IS_WRITABLE_
    ];

    protected $isReadable = [
#__AUTO_GENERATED_IS_WRITABLE_
    ];

    protected $setters = [
#__AUTO_GENERATED_SETTyuiy567tbjERS_
    ];

    protected $collectionSetters = [
#__AUTO_GENERATED_SETTERS_COLLECTION_
    ];

    protected $getters = [
#__AUTO_GENERATED_GETTERS
    ];

    /**
     * {@inheritdoc}
     */
    public function setValue(&$objectOrArray, $propertyPath, $value)
    {
        if (is_array($value) || $value instanceof \Traversable) {
            $method = $this->collectionSetters[$propertyPath];
        } else {
            $method = $this->setters[$propertyPath];
        }

        return $this->{$method}($objectOrArray, $value);
    }
    /**
     * {@inheritdoc}
     */
    public function getValue($objectOrArray, $propertyPath)
    {
        return $this->getters[$propertyPath]($objectOrArray);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable($objectOrArray, $propertyPath)
    {
        if (!isset($this->isWritable[$propertyPath])) {
            throw new \InvalidArgumentException("Property path $propertyPath is not found in isWritable list");
        }

        return $this->isWritable[$propertyPath];
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable($objectOrArray, $propertyPath)
    {
        if (!isset($this->isReadable[$propertyPath])) {
            throw new \InvalidArgumentException("Property path $propertyPath is not found in isReadable list");
        }

        return $this->isReadable[$propertyPath];
    }

#__AUTO_GENERATED_FUNCTdsfsIONS_SETTERS_

#__AUTO_GENERATED_FUNCTION_COLLECTION_SETTERS_

#__AUTO_GENERATED_FUNCTION_GETTERS_

}