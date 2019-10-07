<?php

namespace AwdStudio\Tests\ServiceBuses\CommandBus;

use AwdStudio\ServiceBuses\CommandBus\CommandBus;
use AwdStudio\ServiceBuses\CommandBus\CommandBusInterface;
use AwdStudio\ServiceBuses\Exception\HandlerNotDefined;
use AwdStudio\ServiceBuses\Exception\WrongMessage;
use AwdStudio\ServiceBuses\Implementation\Middleware\Chain;
use AwdStudio\Tests\BusTestCase;

/**
 * @coversDefaultClass \AwdStudio\ServiceBuses\CommandBus\CommandBus
 */
class CommandBusTest extends BusTestCase
{

    /** @var \AwdStudio\ServiceBuses\CommandBus\CommandBus */
    private $instance;

    public function setUp(): void
    {
        parent::setUp();

        $handlers = $this->getHandlersMock();
        $middlewareChain = $this->getMiddlewareMock();

        $this->instance = new CommandBus($handlers, $middlewareChain);
    }

    /**
     * @covers ::__construct
     */
    public function testInstance()
    {
        $this->assertInstanceOf(CommandBusInterface::class, $this->instance);
    }

    /**
     * @covers ::handle
     * @covers ::run
     * @covers ::firstHandler
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
     * @covers ::firstHandler
     * @covers ::resolveHandlers
     * @covers ::validateCommand
     * @covers ::execute
     */
    public function testHandleNoHandlers()
    {
        $middlewareChain = $this->getMiddlewareMock();
        $handlers = $this->getHandlersMock();
        $handlers
            ->expects($this->any())
            ->method('get')
            ->willReturn([]);

        $instance = new CommandBus($handlers, $middlewareChain);

        $this->expectException(HandlerNotDefined::class);
        $instance->handle(new \stdClass());
    }

    /**
     * @covers ::handle
     * @covers ::run
     * @covers ::firstHandler
     * @covers ::resolveHandlers
     * @covers ::validateCommand
     * @covers ::execute
     */
    public function testHandleWithMiddleware()
    {
        $command = new class
        {
            public $isDone = false;
            public $calledTimes = 0;
        };

        $middleware = new class
        {
            public function __invoke($command, callable $next)
            {
                $command->calledTimes++;
                return $next($command);
            }
        };

        $handler = new class
        {
            public function __invoke($command)
            {
                $command->isDone = true;
                $command->calledTimes++;
            }
        };

        $handlers = $this->getHandlersMock();
        $handlers
            ->expects($this->any())
            ->method('get')
            ->willReturn([$handler]);

        $middlewareChain = new Chain();
        $middlewareChain->add($middleware);

        $instance = new CommandBus($handlers, $middlewareChain);
        $instance->handle($command);

        $this->assertTrue($command->isDone);
        $this->assertEquals(2, $command->calledTimes);
    }

}
