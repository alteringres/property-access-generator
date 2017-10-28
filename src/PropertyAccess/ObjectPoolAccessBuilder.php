<?php

namespace Ingres\PropertyAccess;

/**
 * Class ObjectPoolAccessBuilder
 * @package Ingres\Symfony\PropertyAccess
 */
class ObjectPoolAccessBuilder
{
    /**
     * @param array $objectAccessCollection
     * @return array
     * @throws \Exception
     */
    public function generatePoolAccess(
        array $objectAccessCollection
    ): array {

        $classAccessorCollection = '';
        $reflector = new \ReflectionClass(ObjectPoolPropertyAccess::class);
        $templatePath = $reflector->getFileName();
        $classContent = file_get_contents($templatePath);
        $className = 'GeneratedPoolPropertyAccess';

        /** @var ObjectAccess $objectAcess */
        foreach ($objectAccessCollection as $objectAccess) {
            $class      = $objectAccess->getClass();
            $attributes = $objectAccess->getAttributes();

            if (!is_object($class)) {
                throw new \Exception("Only objects can have property access pool auto generated");
            }

            $objectAccessBuilder = new ObjectAccessBuilder();
            $classAccessor = $objectAccessBuilder->generatePropertiesAccess($class, $attributes);

            $splitContentMark = '#remove_all_before_this';
            $constructorCallMark = '#__CALL_CONSTRUCTOR__';
            $classAccessor = str_replace($constructorCallMark, '()', $classAccessor);
            $removeBeforePos = strpos($classAccessor, $splitContentMark);
            if (false !== $removeBeforePos) {
                $classAccessor = substr($classAccessor, $removeBeforePos + strlen($splitContentMark) + 1);
            }
            $classAccessorCollection .= (empty($classAccessorCollection)) ? '' : ",\n";
            $classAccessorCollection .= '"' . get_class($class) . '"    =>  new ' . $classAccessor;
        }

        $classContent = str_replace(
            '#__AUTO_GENERATE_CLASS_INSTANCES__',
            $classAccessorCollection,
            $classContent
        );

        $classContent = str_replace(
            'class ObjectPoolPropertyAccess',
            'class ' . $className,
            $classContent
        );

        return [ $classContent, $className ];
    }
}