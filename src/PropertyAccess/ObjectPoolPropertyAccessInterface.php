<?php

namespace Ingres\PropertyAccess;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Interface ObjectPoolPropertyAccessInterface
 * @package Ingres\PropertyAccess
 */
interface ObjectPoolPropertyAccessInterface extends PropertyAccessorInterface
{
    /**
     * @param $object
     * @param $property
     * @param $operation
     * @return mixed
     */
    public function hasAccessToOperation($object, $property, $operation);
}