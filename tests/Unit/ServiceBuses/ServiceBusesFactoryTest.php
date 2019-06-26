<?php

namespace AwdStudio\Tests\Unit\ServiceBuses;

use AwdStudio\ServiceBuses\CommandBus\CommandBusInterface;
use AwdStudio\ServiceBuses\EventBus\EventBusInterface;
use AwdStudio\ServiceBuses\QueryBus\QueryBusInterface;
use AwdStudio\ServiceBuses\ServiceBusesFactory;
use AwdStudio\Tests\Mock\MockDIContainer;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \AwdStudio\ServiceBuses\ServiceBusesFactory
 */
class ServiceBusesFactoryTest extends TestCase
{

    /**
     * Instance.
     *
     * @var \AwdStudio\ServiceBuses\ServiceBusesFactory
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
        $this->instance = new ServiceBusesFactory($this->container);
    }

    /**
     * @covers ::__construct
     */
    public function testInstance()
    {
        $this->assertInstanceOf(ServiceBusesFactory::class, $this->instance);
    }

    /**
     * @covers ::commandBus
     */
    public function testCommandBus()
    {
        $this->assertInstanceOf(CommandBusInterface::class, $this->instance->commandBus());
        $this->assertInstanceOf(CommandBusInterface::class, $this->instance->commandBus([]));
        $this->assertInstanceOf(CommandBusInterface::class, $this->instance->commandBus([
            \stdClass::class => \get_class(new class {}),
        ]));
    }

    /**
     * @covers ::eventBus
     */
    public function testEventBus()
    {
        $this->assertInstanceOf(EventBusInterface::class, $this->instance->eventBus());
        $this->assertInstanceOf(EventBusInterface::class, $this->instance->eventBus([]));
        $this->assertInstanceOf(EventBusInterface::class, $this->instance->eventBus([
            \stdClass::class => \get_class(new class {}),
        ]));
    }

    /**
     * @covers ::queryBus
     */
    public function testQueryBus()
    {
        $this->assertInstanceOf(QueryBusInterface::class, $this->instance->queryBus());
        $this->assertInstanceOf(QueryBusInterface::class, $this->instance->queryBus([]));
        $this->assertInstanceOf(QueryBusInterface::class, $this->instance->queryBus([
            \stdClass::class => [
                \get_class(new class {})
            ],
        ]));
    }

}
