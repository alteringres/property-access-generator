<?php

namespace Ingres\PropertyAccess;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

/**
 * Class ObjectAccessBuilder
 * @package Ingres\Symfony\PropertyAccess
 */
class ObjectAccessBuilder
{
    const READ_FUNCTION_PREFIX = ObjectAwarePropertyAccessor::READ_FUNCTION_PREFIX;

    const WRITE_FUNCTION_PREFIX = ObjectAwarePropertyAccessor::WRITE_FUNCTION_PREFIX;

    const WRITE_COLLECTION_FUNCTION_PRFIX = ObjectAwarePropertyAccessor::WRITE_COLLECTION_FUNCTION_PRFIX;

    /**
     * @var array
     */
    protected $readPropertyAccess = [];

    /**
     * @var array
     */
    protected $writePropertyAccess = [];

    /**
     * @var array
     */
    protected $writeCollectionPropertyAccess = [];

    /**
     * @var array
     */
    protected $isWritable = [];

    /**
     * @var array
     */
    protected $isReadable = [];

    /**
     * Generate access file content
     *
     * @param $objectImpl
     * @param array $properties
     * @param $ignoreFaultyProperties
     *
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function generatePropertiesAccess(
        $objectImpl,
        array $properties,
        $ignoreFaultyProperties
    ) {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $object = get_class($objectImpl);

        $writePropertyCacheRefl = new \ReflectionProperty(get_class($propertyAccessor), 'writePropertyCache');
        $writePropertyCacheRefl->setAccessible(true);
        $readPropertyCacheRefl = new \ReflectionProperty(get_class($propertyAccessor), 'readPropertyCache');
        $readPropertyCacheRefl->setAccessible(true);

        $readPropertyCacheRefl->setValue($propertyAccessor, []);
        $writePropertyCacheRefl->setValue($propertyAccessor, []);

        $getReadAccessInfoMethodRefl = new \ReflectionMethod(get_class($propertyAccessor), 'getReadAccessInfo');
        $getWriteAccessInfoMethodRefl = new \ReflectionMethod(get_class($propertyAccessor), 'getWriteAccessInfo');
        $getReadAccessInfoMethodRefl->setAccessible(true);
        $getWriteAccessInfoMethodRefl->setAccessible(true);

        $ignoredProperties = [];

        foreach ($properties as $property) {
            try {
                $readAccessInfo = $getReadAccessInfoMethodRefl->invoke($propertyAccessor, $object, $property);

                $readCall = $this->generateGetValue($readAccessInfo, $object, $property);

                $readPropertyCacheRefl->setValue($propertyAccessor, []);
                $writePropertyCacheRefl->setValue($propertyAccessor, []);

                $writeAccessInfo = $getWriteAccessInfoMethodRefl->invoke($propertyAccessor, $object, $property, null);
                $writeCall = $this->generateWriteValue($writeAccessInfo, $object, $property, null);

                $readPropertyCacheRefl->setValue($propertyAccessor, []);
                $writePropertyCacheRefl->setValue($propertyAccessor, []);

                $writeCollectionAccessInfo = $getWriteAccessInfoMethodRefl->invoke($propertyAccessor, $object, $property, [1]);
                $writeCollectionCall = $this->generateWriteValue($writeCollectionAccessInfo, $object, $property, [1]);


                $isWritable = $propertyAccessor->isWritable($objectImpl, $property);
                $isReadable = $propertyAccessor->isReadable($objectImpl, $property);

                /*
                 * Save data at the end, when no error can occur
                 */
                $this->isReadable[$property] = $isReadable;
                $this->isWritable[$property] = $isWritable;
                $this->writeCollectionPropertyAccess[$property] = $writeCollectionCall;
                $this->writePropertyAccess[$property] = $writeCall;
                $this->readPropertyAccess[$property] = $readCall;

            } catch (\Exception $exception) {
                $ignoredProperties[] = $property;
            }

            if (isset($exception) && !$ignoreFaultyProperties) {
                throw $exception;
            }
        }

        return [ $this->generate(), $ignoredProperties ];
    }

    /**
     * Generates code called when a write operation is called
     *
     * @param $access
     * @param $object
     * @param $property
     * @param $value
     * @return string
     */
    private function generateWriteValue($access, $object, $property, $value)
    {
        if (PropertyAccessor::ACCESS_TYPE_METHOD === $access[PropertyAccessor::ACCESS_TYPE]) {

            return '$objectOrArray->' . $access[PropertyAccessor::ACCESS_NAME] . '($value)';

        } elseif (PropertyAccessor::ACCESS_TYPE_PROPERTY === $access[PropertyAccessor::ACCESS_TYPE]) {

            return '$objectOrArray->' . $access[PropertyAccessor::ACCESS_NAME] . ' = $value';

        } elseif (PropertyAccessor::ACCESS_TYPE_ADDER_AND_REMOVER === $access[PropertyAccessor::ACCESS_TYPE]) {

            $addMethod      = $access[PropertyAccessor::ACCESS_ADDER];
            $removeMethod   = $access[PropertyAccessor::ACCESS_REMOVER];

            return <<<COLLECTION_WRITE
//generated write collection
\$previousValue = \$this->>getValue(\$objectOrArray, \$property);
if (\$previousValue instanceof \Traversable) {
    \$previousValue = iterator_to_array(\$previousValue);
}
if (\$previousValue && is_array(\$previousValue)) {
    if (is_object(\$collection)) {
        \$collection = iterator_to_array(\$collection);
    }
    foreach (\$previousValue as \$key => \$item) {
        if (!in_array(\$item, \$collection, true)) {
            unset(\$previousValue[\$key]);
            \$objectOrArray->$removeMethod(\$item);
        }
    }
} else {
    \$previousValue = false;
}

foreach (\$collection as \$item) {
    if (!\$previousValue || !in_array(\$item, \$previousValue, true)) {
        \$objectOrArray->$addMethod(\$item);
    }
}
COLLECTION_WRITE;

        } elseif (!$access[PropertyAccessor::ACCESS_HAS_PROPERTY] && property_exists($object, $property)) {
            // Needed to support \stdClass instances. We need to explicitly
            // exclude $access[PropertyAccessor::ACCESS_HAS_PROPERTY], otherwise if
            // a *protected* property was found on the class, property_exists()
            // returns true, consequently the following line will result in a
            // fatal error.
            return '$objectOrArray->' . $property . ' = ' . $value;

        } elseif (PropertyAccessor::ACCESS_TYPE_MAGIC === $access[PropertyAccessor::ACCESS_TYPE]) {

            return '$objectOrArray->' . $access[PropertyAccessor::ACCESS_NAME] . '($value)';

        } elseif (PropertyAccessor::ACCESS_TYPE_NOT_FOUND === $access[PropertyAccessor::ACCESS_TYPE]) {
            throw new NoSuchPropertyException(sprintf(
                'Could not determine access type for property "%s" in class "%s".',
                $property,
                is_object($object)
                    ? get_class($object)
                    : (is_string($object) ? $object : gettype($object))
            ));
        } else {
            throw new NoSuchPropertyException($access[PropertyAccessor::ACCESS_NAME]);
        }
    }

    /**
     * Generate code called when a read operation is performed
     *
     * @param array $access
     * @param $object
     * @param $property
     * @return string
     */
    private function generateGetValue(array $access, $object, $property)
    {
        if (PropertyAccessor::ACCESS_TYPE_METHOD === $access[PropertyAccessor::ACCESS_TYPE]) {

            return '$objectOrArray->' . $access[PropertyAccessor::ACCESS_NAME] . "()";

        } elseif (PropertyAccessor::ACCESS_TYPE_PROPERTY === $access[PropertyAccessor::ACCESS_TYPE]) {

            return '$objectOrArray->' . $access[PropertyAccessor::ACCESS_NAME];

        } elseif (!$access[PropertyAccessor::ACCESS_HAS_PROPERTY] && property_exists($object, $property)) {
            // Needed to support \stdClass instances. We need to explicitly
            // exclude $access[PropertyAccessor::ACCESS_HAS_PROPERTY], otherwise if
            // a *protected* property was found on the class, property_exists()
            // returns true, consequently the following line will result in a
            // fatal error.
            return '$objectOrArray->' . $property;

        } elseif (PropertyAccessor::ACCESS_TYPE_MAGIC === $access[PropertyAccessor::ACCESS_TYPE]) {
            // we call the getter and hope the __call do the job
            return '$objectOrArray->' . $access[PropertyAccessor::ACCESS_NAME] . "()";

        }

        throw new NoSuchPropertyException($access[PropertyAccessor::ACCESS_NAME]);
    }

    /**
     * @return bool|mixed|string
     * @internal param $cacheDir
     */
    private function generate()
    {
        $reflector = new \ReflectionClass(ObjectAwarePropertyAccessor::class);
        $templatePath = $reflector->getFileName();
        $classContent = file_get_contents($templatePath);

        if ($classContent === false) {
            throw new \RuntimeException("Class content can not be loaded");
        }

        $classContent = $this->generateIsWritable($classContent);
        $classContent = $this->generateIsReadable($classContent);

        $classContent = $this->generateGettersMap($classContent);
        $classContent = $this->generateSettersMap($classContent);
        $classContent = $this->generateSetterCollectionMap($classContent);

        $classContent = $this->generateClassName($classContent);

        $classContent = $this->generateReadFunctions($classContent);
        $classContent = $this->generateSetFunctions($classContent);
        $classContent = $this->generateSetCollectionFunctions($classContent);

        return $classContent;
    }

    /**
     * @param string $classContent
     * @return mixed
     */
    protected function generateSetCollectionFunctions(string $classContent)
    {
        return str_replace(
            '#__AUTO_GENERATED_FUNCTION_COLLECTION_SETTERS_',
            implode(
                "\n",
                $this->generateFunctions(
                    $this->writeCollectionPropertyAccess,
                    self::WRITE_COLLECTION_FUNCTION_PRFIX,
                    ', $value'
                )
            ),
            $classContent
        );
    }

    /**
     * @param string $classContent
     * @return mixed
     */
    protected function generateSetFunctions(string $classContent)
    {
        return str_replace(
            '#__AUTO_GENERATED_FUNCTdsfsIONS_SETTERS_',
            implode(
                "\n",
                $this->generateFunctions(
                    $this->writePropertyAccess,
                    self::WRITE_FUNCTION_PREFIX,
                    ', $value'
                )
            ),
            $classContent
        );
    }

    /**
     * @param string $classContent
     * @return mixed
     */
    protected function generateReadFunctions(string $classContent)
    {
        return str_replace(
            '#__AUTO_GENERATED_FUNCTION_GETTERS_',
            implode(
                "\n",
                $this->generateFunctions(
                    $this->readPropertyAccess,
                    self::READ_FUNCTION_PREFIX,
                    ''
                )
            ),
            $classContent
        );
    }

    /**
     * @param array $propertyCollection
     * @param $functionPrefix
     * @param string $arguements
     * @return array
     * @internal param callable $fcnNameGenerator
     */
    protected function generateFunctions(array $propertyCollection, $functionPrefix, string $arguements)
    {
        $functions = [];
        foreach ($propertyCollection as $property => $propertyAccess) {
            $functionName = $functionPrefix . $property;
            $functions[] =<<<READ_FUNCTION

    // function autoGenerated
    public function $functionName( \$objectOrArray $arguements)
    {
        return $propertyAccess;
    }
    
READ_FUNCTION;
        }

        return $functions;
    }

    /**
     * @param string $classContent
     * @return mixed
     */
    protected function generateClassName(string $classContent)
    {
        return str_replace('class ObjectAwarePropertyAccessor', 'class ', $classContent);
    }

    /**
     * @param string $classContent
     * @return mixed
     */
    protected function generateSetterCollectionMap(string $classContent)
    {
        return $this->generateFunctionMap(
            $classContent,
            '#__AUTO_GENERATED_SETTERS_COLLECTION_',
            self::WRITE_COLLECTION_FUNCTION_PRFIX,
            $this->writeCollectionPropertyAccess
        );
    }

    /**
     * @param string $classContent
     * @return mixed
     */
    protected function generateSettersMap(string $classContent)
    {
        return $this->generateFunctionMap(
            $classContent,
            '#__AUTO_GENERATED_SETTyuiy567tbjERS_',
            self::WRITE_FUNCTION_PREFIX,
            $this->writePropertyAccess
        );
    }

    /**
     * @param string $classContent
     * @return mixed
     */
    protected function generateGettersMap(string $classContent)
    {
        return $this->generateFunctionMap(
            $classContent,
            '#__AUTO_GENERATED_GETTERS',
            self::READ_FUNCTION_PREFIX,
            $this->readPropertyAccess
        );
    }

    /**
     * @param string $classContent
     * @param $replace
     * @param $functionPrefix
     * @return mixed
     */
    protected function generateFunctionMap(string $classContent, $replace, $functionPrefix, array $collection)
    {
        $map = [];
        foreach ($collection as $property => $propertyAccess) {
            $map[] = "'$property' => '" . $functionPrefix . $property . "'";
        }

        $mapString = implode(",\n", $map);

        return str_replace($replace, $mapString, $classContent);
    }

    /**
     * @param string $classContent
     * @return mixed
     */
    protected function generateIsReadable(string $classContent)
    {
        $isReadableArray = [];
        foreach ($this->isReadable as $property => $isReadable) {
            $isReadableArray[] = "'{$property}' => " . (($isReadable) ? 'true' : 'false') . ',';
        }
        return str_replace('#__AUTO_GENERATED_IS_WRITABLE_', implode("\n", $isReadableArray), $classContent);
    }

    /**
     * @param $classContent
     * @return mixed
     */
    protected function generateIsWritable(string $classContent)
    {
        $isWritableArray = [];
        foreach ($this->isWritable as $property => $isWritable) {
            $isWritableArray[] = "'{$property}' => " . (($isWritable) ? 'true' : 'false') . ',';
        }

        return str_replace('#__AUTO_GENERATED_IS_WRITABLE_', implode("\n", $isWritableArray), $classContent);
    }
}