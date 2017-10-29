<?php

namespace Ingres\PropertyAccess;

use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Class BridgePropertyAccess
 * @package Ingres\PropertyAccess
 */
class BridgePropertyAccess implements PropertyAccessorInterface
{
    /** @var  ObjectPoolPropertyAccess */
    protected $objectPoolPropertyAccess;

    /** @var  PropertyAccessor */
    protected $nativePropertyAccessor;

    /** @var  LoggerInterface */
    protected $logger;

    /** @var bool  */
    protected $usePool = true;

    /** @var bool  */
    protected $canLog = false;

    /**
     * @param ObjectPoolPropertyAccessInterface $propertyAccess
     */
    public function setObjectPoolPropertyAccess(ObjectPoolPropertyAccessInterface $propertyAccess)
    {
        $this->objectPoolPropertyAccess = $propertyAccess;
    }

    /**
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function setNativePropertyAccess(PropertyAccessorInterface $propertyAccessor)
    {
        $this->nativePropertyAccessor = $propertyAccessor;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param bool $usePool
     */
    public function setUsePool(bool $usePool)
    {
        $this->usePool = $usePool;
    }

    /**
     * @param bool $canLog
     */
    public function setCanLog(bool $canLog)
    {
        $this->canLog = $canLog;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(&$objectOrArray, $propertyPath, $value)
    {
        $objectPoolHasAccess = $this->usePool && $this->objectPoolPropertyAccess->hasAccessToOperation(
            $objectOrArray,
            $propertyPath,
            ObjectAccess::OPERATION_SET
        );
        if ($objectPoolHasAccess) {
            return $this->objectPoolPropertyAccess->setValue($objectOrArray, $propertyPath, $value);
        } else {
            if ($this->canLog) {
                $this->logger->error(sprintf(
                        "Property access not granted by pool, property: %s, class: %s",
                        $propertyPath,
                        get_class($objectOrArray))
                );
            }
            return $this->nativePropertyAccessor->setValue($objectOrArray, $propertyPath, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($objectOrArray, $propertyPath)
    {
        $objectPoolHasAccess = $this->usePool && $this->objectPoolPropertyAccess->hasAccessToOperation(
            $objectOrArray,
            $propertyPath,
            ObjectAccess::OPERATION_GET
        );

        if ($objectPoolHasAccess) {
            return $this->objectPoolPropertyAccess->getValue($objectOrArray, $propertyPath);
        } else {
            if ($this->canLog) {
                $this->logger->error(sprintf(
                        "Property access not granted by pool, property: %s, class: %s",
                        $propertyPath,
                        get_class($objectOrArray))
                );
            }
            return $this->nativePropertyAccessor->getValue($objectOrArray, $propertyPath);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable($objectOrArray, $propertyPath)
    {
        $objectPoolHasAccess = $this->usePool && $this->objectPoolPropertyAccess->hasAccessToOperation(
            $objectOrArray,
            $propertyPath,
            ObjectAccess::OPERATION_GET
        );
        if ($objectPoolHasAccess) {
            return $this->objectPoolPropertyAccess->isWritable($objectOrArray, $propertyPath);
        } else {
            if ($this->canLog) {
                $this->logger->error(sprintf(
                        "Property access not granted by pool, property: %s, class: %s",
                        $propertyPath,
                        get_class($objectOrArray))
                );
            }
            return $this->nativePropertyAccessor->isWritable($objectOrArray, $propertyPath);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable($objectOrArray, $propertyPath)
    {
        $objectPoolHasAccess = $this->usePool && $this->objectPoolPropertyAccess->hasAccessToOperation(
            $objectOrArray,
            $propertyPath,
            ObjectAccess::OPERATION_GET
        );
        if ($objectPoolHasAccess) {
            return $this->objectPoolPropertyAccess->isReadable($objectOrArray, $propertyPath);
        } else {
            if ($this->canLog) {
                $this->logger->error(sprintf(
                        "Property access not granted by pool, property: %s, class: %s",
                        $propertyPath,
                        get_class($objectOrArray))
                );
            }
            return $this->nativePropertyAccessor->isReadable($objectOrArray, $propertyPath);
        }
    }
}