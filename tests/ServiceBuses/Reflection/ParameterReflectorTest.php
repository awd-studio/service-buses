<?php

namespace AwdStudio\Tests\ServiceBuses\Reflection;

use AwdStudio\ServiceBuses\Reflection\ParameterReflector;
use AwdStudio\Tests\ServiceBuses\Stub\Foo;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \AwdStudio\ServiceBuses\Reflection\ParameterReflector
 */
class ParameterReflectorTest extends TestCase
{

    /**
     * @covers ::__construct
     * @covers ::canBeProcessed
     * @covers ::name
     */
//    public function testNoName()
//    {
//        $dependency = new class
//        {
//            public function foo(\stdClass $bar) { }
//        };
//
//        $parameter = $this->invokeFirstParameter($dependency, 'foo');
//
//        $reflector = new ParameterReflector($parameter);
//        $this->assertNull($reflector->name());
//    }

    /**
     * @covers ::__construct
     * @covers ::canBeProcessed
     * @covers ::name
     */
    public function testUndefinedName()
    {
        $dependency = new class
        {
            public function foo(UndefinedDependency $bar) { }
        };

        $parameter = $this->invokeFirstParameter($dependency, 'foo');

        $reflector = new ParameterReflector($parameter);
        $this->assertNull($reflector->name());
    }

    /**
     * @covers ::__construct
     * @covers ::canBeProcessed
     * @covers ::name
     */
    public function testNameOk()
    {
        $dependency = new class
        {
            public function foo(Foo $bar) { }
        };

        $parameter = $this->invokeFirstParameter($dependency, 'foo');

        $reflector = new ParameterReflector($parameter);
        $this->assertSame(Foo::class, $reflector->name());
    }

    private function invokeFirstParameter($class, $method): \ReflectionParameter
    {
        $reflection = new \ReflectionMethod($class, $method);
        foreach ($reflection->getParameters() as $parameter) {
            return $parameter;
        }

        throw new \Exception('No parameter defined');
    }

}
