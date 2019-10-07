<?php

namespace AwdStudio\Tests\ServiceBuses\Implementation\Middleware;

use AwdStudio\ServiceBuses\Core\Middleware\MiddlewareChain;
use AwdStudio\ServiceBuses\Implementation\Middleware\Chain;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \AwdStudio\ServiceBuses\Implementation\Middleware\Chain
 */
class ChainTest extends TestCase
{

    /**
     * @covers ::__construct
     * @covers ::add
     * @covers ::chain
     */
    public function testChain()
    {
        $instance = new Chain();
        $this->assertInstanceOf(MiddlewareChain::class, $instance);

        $handler = new class
        {
            public function __invoke($command) {}
        };

        $middleware1 = new class
        {
            public $value = false;
            public function __invoke($command, $next)
            {
                $command->value++;
                $this->value = true;

                return $next($command);
            }
        };

        $middleware2 = new class
        {
            public $value = 0;
            public function __invoke($command, $next)
            {
                $command->value++;
                $this->value++;
                $var = $next($command);
                $this->value++;

                return $var;
            }
        };

        $instance->add($middleware1);
        $instance->add($middleware2);

        $command = new \stdClass;
        $command->value = 0;

        $chain = $instance->chain($command, $handler);

        $chain($command);

        $this->assertTrue($middleware1->value);
        $this->assertEquals(2, $middleware2->value);
        $this->assertEquals(2, $command->value);
    }

}
