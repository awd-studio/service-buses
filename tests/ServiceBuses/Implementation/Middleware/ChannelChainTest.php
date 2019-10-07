<?php

namespace AwdStudio\Tests\ServiceBuses\Implementation\Middleware;

use AwdStudio\ServiceBuses\Core\Middleware\MiddlewareChain;
use AwdStudio\ServiceBuses\Implementation\Middleware\ChannelChain;
use AwdStudio\Tests\ServiceBuses\Stub\Foo;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \AwdStudio\ServiceBuses\Implementation\Middleware\ChannelChain
 */
class ChannelChainTest extends TestCase
{
    /** @var \AwdStudio\ServiceBuses\Implementation\Middleware\ChannelChain */
    private $instance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new ChannelChain();
    }

    /**
     * @covers ::__construct
     */
    public function testInstantiate()
    {
        $this->assertInstanceOf(MiddlewareChain::class, $this->instance);
    }

    /**
     * @covers ::chain
     * @covers ::add
     * @covers ::addToChannel
     * @covers ::chainForChannel
     * @covers ::resolveChain
     */
    public function testAdd()
    {
        $message1 = new \stdClass;
        $message2 = new class {};
        $message3 = new Foo;

        $handler = new class {
            public function __invoke($command) {}
        };

        $middleware1 = new class
        {
            public $v = 0;

            public function __invoke(\stdClass $message, $next)
            {
                $this->v++;
                return $next($message);
            }
        };

        $middleware2 = new class
        {
            public $v = 0;

            public function __invoke($message, $next) // No type of message defined
            {
                $this->v++;
                return $next($message);
            }
        };

        $middleware3 = new class
        {
            public $v = 0;

            public function __invoke(Foo $message, $next)
            {
                $this->v++;
                return $next($message);
            }
        };

        $this->instance->add($middleware1);
        $this->instance->add($middleware2);
        $this->instance->add($middleware3);

        $chain1 = $this->instance->chain($message1, $handler);
        $chain2 = $this->instance->chain($message2, $handler);
        $chain3 = $this->instance->chain($message3, $handler);

        $chain1($message1);

        $this->assertEquals(1, $middleware1->v);
        $this->assertEquals(1, $middleware2->v);
        $this->assertEquals(0, $middleware3->v);

        $chain2($message2);

        $this->assertEquals(1, $middleware1->v);
        $this->assertEquals(2, $middleware2->v);
        $this->assertEquals(0, $middleware3->v);

        $chain3($message3);

        $this->assertEquals(1, $middleware1->v);
        $this->assertEquals(3, $middleware2->v);
        $this->assertEquals(1, $middleware3->v);
    }

}
