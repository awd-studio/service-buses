<?php

namespace AwdStudio\Tests\ServiceBuses\Reflection;

use AwdStudio\ServiceBuses\Reflection\ParameterIsProcessable;
use AwdStudio\Tests\ServiceBuses\Stub\Foo;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \AwdStudio\ServiceBuses\Reflection\ParameterIsProcessable
 */
class ParameterIsProcessableTest extends TestCase
{

    /**
     * @covers ::create
     * @covers ::__construct
     * @covers ::process
     * @covers ::processClass
     * @covers ::resolveTypeClass
     * @covers ::isProcessable
     */
    public function testIsProcessable()
    {
        $customClass = new class {
            public function foo(Foo $bar) {}
        };

        $parameter = $this->invokeFirstParameter($customClass, 'foo');

        $this->assertTrue(ParameterIsProcessable::create($parameter)->isProcessable());
    }

    /**
     * @covers ::create
     * @covers ::__construct
     * @covers ::process
     * @covers ::processClass
     * @covers ::resolveTypeClass
     * @covers ::isProcessable
     */
    public function testIsNotProcessableNoTypeDefinition()
    {
        $customClass = new class {
            public function foo($bar) {}
        };

        $parameter = $this->invokeFirstParameter($customClass, 'foo');

        $this->assertFalse(ParameterIsProcessable::create($parameter)->isProcessable());
    }

    /**
     * @covers ::create
     * @covers ::__construct
     * @covers ::process
     * @covers ::processClass
     * @covers ::resolveTypeClass
     * @covers ::isProcessable
     */
    public function testIsNotProcessableUndefinedDependency()
    {
        $customClass = new class {
            public function foo(UndefinedDependency $bar) {}
        };

        $parameter = $this->invokeFirstParameter($customClass, 'foo');

        $this->assertFalse(ParameterIsProcessable::create($parameter)->isProcessable());
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
