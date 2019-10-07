<?php

namespace AwdStudio\Tests\ServiceBuses\Reflection;

use AwdStudio\ServiceBuses\Reflection\Reflector;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \AwdStudio\ServiceBuses\Reflection\Reflector
 */
class ReflectorTest extends TestCase
{

    /**
     * @covers ::create
     * @covers ::__construct
     */
    public function testCreateFromInvokable()
    {
        $middleware = new class
        {
            public function __invoke() { }
        };

        $instance = Reflector::create($middleware);
        $this->assertInstanceOf(Reflector::class, $instance);
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    public function testCreateFromCallableArray()
    {
        $middleware = new class
        {
            public function __invoke() { }
        };

        $this->assertInstanceOf(Reflector::class, Reflector::create([$middleware, '__invoke']));
        $this->assertInstanceOf(Reflector::class, Reflector::create($middleware));
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    public function testCreateFromFunction()
    {
        $middleware = function () { };

        $this->assertInstanceOf(Reflector::class, Reflector::create($middleware));
        $this->assertInstanceOf(Reflector::class, Reflector::create('phpinfo'));
    }

    /**
     * @covers ::create
     * @covers ::__construct
     * @covers ::firstParametersTypeName
     * @covers ::resolveInvokableArgument
     * @covers ::getFirsParameter
     */
    public function testFirstParametersTypeNameNoType()
    {
        $middleware = new class
        {
            public function __invoke($foo) { }
        };

        $this->assertSame(
            Reflector::NO_INVOKABLE_ARGUMENT,
            Reflector::create($middleware)->firstParametersTypeName()
        );
    }

    /**
     * @covers ::create
     * @covers ::__construct
     * @covers ::firstParametersTypeName
     * @covers ::resolveInvokableArgument
     * @covers ::getFirsParameter
     */
    public function testFirstParameterNoParameter()
    {
        $middleware = new class
        {
            public function __invoke() { }
        };

        $this->assertSame(
            Reflector::NO_INVOKABLE_ARGUMENT,
            Reflector::create($middleware)->firstParametersTypeName()
        );
    }

    /**
     * @covers ::create
     * @covers ::__construct
     * @dataProvider callableProvider
     */
    public function testConstructor(callable $callback)
    {
        $instance = Reflector::create($callback);
        $this->assertInstanceOf(Reflector::class, $instance);
    }

    public function callableProvider()
    {
        $middleware = new class
        {
            public function __invoke() { }   // Method to make a class callable
            public function foo() { }        // Method to call class dynamically
            public static function bar() { } // Method to call class statically
        };

        $middlewareClass = \get_class($middleware);

        $closure = function () { };

        return [
            [$middleware],                      // Invokable class
            [[$middleware, 'foo']],             // Dynamical callback
            [[$middlewareClass, 'bar']],        // Statical callback
            ['phpinfo'],                        // Declared function
            [$closure],                         // Lambda
            [\Closure::fromCallable($closure)], // Closure instance
        ];
    }

}
