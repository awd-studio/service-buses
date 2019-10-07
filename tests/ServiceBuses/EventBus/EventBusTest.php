<?php

namespace AwdStudio\Tests\ServiceBuses\EventBus;

use AwdStudio\ServiceBuses\EventBus\EventBus;
use AwdStudio\ServiceBuses\EventBus\EventBusInterface;
use AwdStudio\ServiceBuses\Exception\WrongMessage;
use AwdStudio\ServiceBuses\Implementation\Middleware\Chain;
use AwdStudio\Tests\BusTestCase;

/**
 * @coversDefaultClass \AwdStudio\ServiceBuses\EventBus\EventBus
 */
class EventBusTest extends BusTestCase
{

    /** @var \AwdStudio\ServiceBuses\EventBus\EventBus */
    private $instance;

    public function setUp(): void
    {
        parent::setUp();

        $handlers = $this->getHandlersMock();
        $middlewareChain = $this->getMiddlewareMock();

        $this->instance = new EventBus($handlers, $middlewareChain);
    }

    /**
     * @covers ::__construct
     */
    public function testInstance()
    {
        $this->assertInstanceOf(EventBusInterface::class, $this->instance);
    }

    /**
     * @covers ::handle
     * @covers ::run
     * @covers ::resolveHandlers
     * @covers ::validateCommand
     * @covers ::execute
     */
    public function testHandleWrongCommand()
    {
        $this->expectException(WrongMessage::class);

        $this->instance->run('not an object');
    }

    /**
     * @covers ::handle
     * @covers ::run
     * @covers ::resolveHandlers
     * @covers ::validateCommand
     * @covers ::execute
     * @doesNotPerformAssertions
     */
    public function testHandleNoHandlersNoExceptions()
    {
        $middlewareChain = $this->getMiddlewareMock();
        $handlers = $this->getHandlersMock();
        $handlers
            ->expects($this->any())
            ->method('get')
            ->willReturn([]);

        $instance = new EventBus($handlers, $middlewareChain);

        $instance->handle(new \stdClass());
    }

    /**
     * @covers ::handle
     * @covers ::run
     * @covers ::resolveHandlers
     * @covers ::validateCommand
     * @covers ::execute
     */
    public function testHandleWithMiddleware()
    {
        $event = new class
        {
            public $value = 42;
            public $calledTimes = 0;
        };

        $middleware = new class
        {
            public $value = null;

            public function __invoke($event, callable $next)
            {
                $this->value = $event->value;
                $event->calledTimes++;
                return $next($event);
            }
        };

        $handler = new class
        {
            public $value = null;

            public function __invoke($event)
            {
                $this->value = $event->value;
                $event->calledTimes++;
            }
        };

        $handlers = $this->getHandlersMock();
        $handlers
            ->expects($this->any())
            ->method('get')
            ->willReturn([$handler]);

        $middlewareChain = new Chain();
        $middlewareChain->add($middleware);

        $instance = new EventBus($handlers, $middlewareChain);
        $instance->handle($event);

        $this->assertEquals(2, $event->calledTimes);
        $this->assertSame($event->value, $middleware->value);
        $this->assertSame($event->value, $handler->value);
    }

}