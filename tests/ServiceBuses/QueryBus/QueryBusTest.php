<?php

namespace AwdStudio\Tests\ServiceBuses\QueryBus;

use AwdStudio\ServiceBuses\QueryBus\QueryBus;
use AwdStudio\ServiceBuses\QueryBus\QueryBusInterface;
use AwdStudio\ServiceBuses\Exception\HandlerNotDefined;
use AwdStudio\ServiceBuses\Exception\WrongMessage;
use AwdStudio\ServiceBuses\Implementation\Middleware\Chain;
use AwdStudio\Tests\BusTestCase;

/**
 * @coversDefaultClass \AwdStudio\ServiceBuses\QueryBus\QueryBus
 */
class QueryBusTest extends BusTestCase
{

    /**
     * Instance.
     *
     * @var \AwdStudio\ServiceBuses\QueryBus\QueryBus
     */
    private $instance;


    /**
     * Settings up.
     */
    public function setUp(): void
    {
        parent::setUp();

        $handlers = $this->getHandlersMock();
        $middlewareChain = $this->getMiddlewareMock();

        $this->instance = new QueryBus($handlers, $middlewareChain);
    }

    /**
     * @covers ::handle
     */
    public function testInstance()
    {
        $this->assertInstanceOf(QueryBusInterface::class, $this->instance);
    }

    /**
     * @covers ::handle
     * @covers ::run
     * @covers ::firstHandler
     * @covers ::resolveHandlers
     * @covers ::validateCommand
     * @covers ::execute
     */
    public function testHandleWrongQuery()
    {
        $this->expectException(WrongMessage::class);

        $this->instance->run('not an query');
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

        $instance = new QueryBus($handlers, $middlewareChain);

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
        $query = new class
        {
            public $value = 42;
        };

        $middleware = new class
        {
            public $isCalled = false;

            public function __invoke($query, callable $next)
            {
                $this->isCalled = true;
                return $next($query);
            }
        };

        $handler = new class
        {
            public $returnValue = 'foo';
            public $queryValue = null;

            public function __invoke($query)
            {
                $this->queryValue = $query->value;
                return $this->returnValue;
            }
        };

        $handlers = $this->getHandlersMock();
        $handlers
            ->expects($this->any())
            ->method('get')
            ->willReturn([$handler]);

        $middlewareChain = new Chain();
        $middlewareChain->add($middleware);

        $instance = new QueryBus($handlers, $middlewareChain);
        $result = $instance->handle($query);

        $this->assertTrue($middleware->isCalled);
        $this->assertSame($query->value, $handler->queryValue);
        $this->assertSame($handler->returnValue, $result);
    }

}
