<?php

namespace AwdStudio\Tests\Unit\ServiceBuses\QueryBus;

use AwdStudio\ServiceBuses\QueryBus\Exception\QueryHandlerIsNotAppropriate;
use AwdStudio\ServiceBuses\QueryBus\Exception\QueryHandlerNotDefined;
use AwdStudio\ServiceBuses\QueryBus\QueryBus;
use AwdStudio\ServiceBuses\QueryBus\QueryBusInterface;
use AwdStudio\Tests\Mock\MockDIContainer;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \AwdStudio\ServiceBuses\QueryBus\QueryBus
 */
class QueryBusTest extends TestCase
{

    /**
     * Instance.
     *
     * @var \AwdStudio\ServiceBuses\QueryBus\QueryBusInterface
     */
    private $instance;

    /** @var \Psr\Container\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $container;


    /**
     * Settings up.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->container = MockDIContainer::getMock($this);
        $this->instance = new QueryBus($this->container);
    }

    /**
     * @covers ::__construct
     */
    public function testCommandBusInstance()
    {
        $this->assertInstanceOf(QueryBusInterface::class, $this->instance);
    }

    /**
     * @covers ::subscribe
     */
    public function testSubscribe()
    {
        $instance = $this->instance->subscribe(\get_class(new class{}), \stdClass::class);

        $this->assertInstanceOf(QueryBusInterface::class, $instance);
    }

    /**
     * @covers ::subscribe
     * @covers ::handle
     * @covers ::resolveHandlerNameByQuery
     * @covers ::validateHandler
     */
    public function testSubscribeHandle()
    {
        // Sample handler
        $handler = new class
        {
            // Required method
            public function fetch(\stdClass $query)
            {
                return 42;
            }
        };

        // Subscribe a handler to a sample command
        $this->instance->subscribe(\get_class($handler), \stdClass::class);

        // Feed a handler with a DI-container to the Bus
        $this->container
            ->expects($this->any())
            ->method('get')
            ->willReturn($handler);

        // Executing
        $result = $this->instance->handle(new \stdClass());

        $this->assertSame(42, $result);
    }

    /**
     * @covers ::handle
     * @covers ::resolveHandlerNameByQuery
     */
    public function testHandleWrongCommand()
    {
        $this->expectException(QueryHandlerNotDefined::class);

        $this->instance->handle(new \stdClass());
    }

    /**
     * @covers ::handle
     * @covers ::resolveHandlerNameByQuery
     * @covers ::validateHandler
     */
    public function testHandleWrongHandler()
    {
        $this->expectException(QueryHandlerIsNotAppropriate::class);

        // Prepare a sample handler
        $handler = new class {};

        // Subscribe a handler to a sample command
        $this->instance->subscribe(\get_class($handler), \stdClass::class);

        // Feed a handler with a DI-container to the Bus
        $this->container
            ->expects($this->any())
            ->method('get')
            ->willReturn($handler);

        $this->instance->handle(new \stdClass());
    }

}
