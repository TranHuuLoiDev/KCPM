<?php
namespace Tests\Support;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

abstract class IsolatedServiceTestCase extends TestCase
{
    protected function service(string $class, array $properties): object
    {
        $reflection = new ReflectionClass($class);
        $service = $reflection->newInstanceWithoutConstructor();
        foreach ($properties as $name => $value) {
            $property = $reflection->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($service, $value);
        }
        return $service;
    }
}
