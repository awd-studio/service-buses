<?php

namespace AwdStudio\Tests\Unit\ServiceBuses\EventBus;

use AwdStudio\ServiceBuses\EventBus\EventBus;
use AwdStudio\ServiceBuses\EventBus\EventBusInterface;
use AwdStudio\ServiceBuses\EventBus\Exception\EventSubscriberIsInappropriate;
use AwdStudio\Tests\Mock\MockDIContainer;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \AwdStudio\ServiceBuses\EventBus\EventBus
 */
class EventBusTest extends TestCase
{

    /**
     * Instance.
     *
     * @var \AwdStudio\ServiceBuses\EventBus\EventBus
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
        $this->instance = new EventBus($this->container);
    }

    /**
     * @covers ::__construct
     */
    public function testEventBusInstance()
    {
        $this->assertInstanceOf(EventBusInterface::class, $this->instance);
    }

    /**
     * @covers ::subscribe
     */
    public function testSubscribe()
    {
        $instance = $this->instance->subscribe(\get_class(new class {}), \stdClass::class);

        $this->assertInstanceOf(EventBusInterface::class, $instance);
    }

    /**
     * @covers ::subscribe
     * @covers ::dispatch
     * @covers ::resolveSubscribers
     * @covers ::validateHandler
     */
    public function testSubscribeHandle()
    {
        // Sample subscriber
        $subscriber1 = new class
        {
            // We will check this state after handling
            public $state = null;

            // Required method
            public function notify(\stdClass $event): void
            {
                $this->state = 'Subscriber 1 is notified';
            }
        };

        // Sample subscriber
        $subscriber2 = new class
        {
            // We will check this state after handling
            public $state = null;

            // Required method
            public function notify(\stdClass $event): void
            {
                $this->state = 'Subscriber 2 is notified';
            }
        };

        // Subscribe subscribers to a sample command
        $this->instance->subscribe(\get_class($subscriber1), \stdClass::class);
        $this->instance->subscribe(\get_class($subscriber2), \stdClass::class);

        // Feed subscribers with a DI-container to the Bus
        $this->container
            ->expects($this->at(0))
            ->method('get')
            ->willReturn($subscriber1);
        $this->container
            ->expects($this->at(1))
            ->method('get')
            ->willReturn($subscriber2);

        // Executing
        $this->instance->dispatch(new \stdClass());

        $this->assertSame('Subscriber 1 is notified', $subscriber1->state);
        $this->assertSame('Subscriber 2 is notified', $subscriber2->state);
    }

    /**
     * @covers ::subscribe
     * @covers ::dispatch
     * @covers ::resolveSubscribers
     * @covers ::validateHandler
     */
    public function testHandleWrongHandler()
    {
        $this->expectException(EventSubscriberIsInappropriate::class);

        // Prepare a sample handler
        $handler = new class {};

        // Subscribe a handler to a sample command
        $this->instance->subscribe(\get_class($handler), \stdClass::class);

        // Feed a handler with a DI-container to the Bus
        $this->container
            ->expects($this->any())
            ->method('get')
            ->willReturn($handler);

        $this->instance->dispatch(new \stdClass());
    }

}
