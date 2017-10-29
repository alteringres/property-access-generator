<?php

namespace Ingres\PropertyAccess;


use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Interface GeneratedPropertyAccessorInterface
 * @package Ingres\PropertyAccess
 */
interface GeneratedPropertyAccessorInterface extends PropertyAccessorInterface
{
    /**
     * Returns true if it has access to a property
     *
     * @param $property
     * @param $operation
     * @return mixed
     */
    public function hasAccessToOperation($property, $operation);
}