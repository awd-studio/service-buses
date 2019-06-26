<?php

namespace AwdStudio\Tests\Unit\ServiceBuses\CommandBus;

use AwdStudio\ServiceBuses\CommandBus\CommandBus;
use AwdStudio\ServiceBuses\CommandBus\CommandBusInterface;
use AwdStudio\ServiceBuses\CommandBus\Exception\CommandHandlerIsInappropriate;
use AwdStudio\ServiceBuses\CommandBus\Exception\CommandHandlerNotDefined;
use AwdStudio\Tests\Mock\MockDIContainer;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \AwdStudio\ServiceBuses\CommandBus\CommandBus
 */
class CommandBusTest extends TestCase
{

    /**
     * Instance.
     *
     * @var \AwdStudio\ServiceBuses\CommandBus\CommandBus
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
        $this->instance = new CommandBus($this->container);
    }

    /**
     * @covers ::__construct
     */
    public function testCommandBusInstance()
    {
        $this->assertInstanceOf(CommandBusInterface::class, $this->instance);
    }

    /**
     * @covers ::subscribe
     */
    public function testSubscribe()
    {
        $instance = $this->instance->subscribe(\get_class(new class {}), \stdClass::class);

        $this->assertInstanceOf(CommandBusInterface::class, $instance);
    }

    /**
     * @covers ::subscribe
     * @covers ::handle
     * @covers ::resolveHandler
     * @covers ::validateHandler
     */
    public function testSubscribeHandle()
    {
        // Sample handler
        $handler = new class
        {
            // We will check this state after handling
            public $state = null;

            // Required method
            public function handle(\stdClass $command): void
            {
                $this->state = $command;
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
        $this->instance->handle(new \stdClass());

        $this->assertInstanceOf(\stdClass::class, $handler->state);
    }

    /**
     * @covers ::handle
     * @covers ::resolveHandler
     */
    public function testHandleWrongCommand()
    {
        $this->expectException(CommandHandlerNotDefined::class);

        $this->instance->handle(new \stdClass());
    }

    /**
     * @covers ::subscribe
     * @covers ::handle
     * @covers ::resolveHandler
     * @covers ::validateHandler
     */
    public function testHandleWrongHandler()
    {
        $this->expectException(CommandHandlerIsInappropriate::class);

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
