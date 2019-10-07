<?php

namespace AwdStudio\Tests\ServiceBuses\Implementation\Handling;

use AwdStudio\ServiceBuses\Exception\HandlerNotDefined;
use AwdStudio\ServiceBuses\Implementation\Handling\ContainerHandlerLocator;
use AwdStudio\Tests\BusTestCase;

/**
 * @coversDefaultClass \AwdStudio\ServiceBuses\Implementation\Handling\ContainerHandlerLocator
 */
class ContainerHandlerLocatorTest extends BusTestCase
{

    /**
     * @covers ::__construct
     * @covers ::add
     * @covers ::get
     * @covers ::hasHandler
     * @covers ::resolveForMessage
     */
    public function testGet()
    {
        $handler = function ($command) { };

        $container = $this->getContainerMock();
        $container
            ->expects($this->any())
            ->method('has')
            ->willReturn(true);
        $container
            ->expects($this->any())
            ->method('get')
            ->willReturn($handler);

        $instance = new ContainerHandlerLocator($container);
        $instance->add('foo', \get_class($handler));

        foreach ($instance->get('foo') as $item) {
            $this->assertSame($handler, $item);
        }
    }

    /**
     * @covers ::__construct
     * @covers ::add
     */
    public function testAddWrongHandler()
    {
        $container = $this->getContainerMock();
        $container
            ->expects($this->any())
            ->method('has')
            ->willReturn(false);

        $instance = new ContainerHandlerLocator($container);

        $this->expectException(HandlerNotDefined::class);
        $instance->add('foo', 'not a handler');
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::hasHandler
     * @covers ::resolveForMessage
     */
    public function testGetNoMessage()
    {
        $container = $this->getContainerMock();
        $container
            ->expects($this->any())
            ->method('has')
            ->willReturn(false);

        $instance = new ContainerHandlerLocator($container);

        $this->expectException(HandlerNotDefined::class);
        $instance->get('foo');
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::hasHandler
     * @covers ::resolveForMessage
     */
    public function testGetNoHandlers()
    {
        $handler = function ($command) { };

        $container = $this->getContainerMock();
        $container
            ->expects($this->at(0))
            ->method('has')
            ->willReturn(true);
        $container
            ->expects($this->at(1))
            ->method('has')
            ->willReturn(false);

        $instance = new ContainerHandlerLocator($container);
        $instance->add('foo', \get_class($handler));

        $this->expectException(HandlerNotDefined::class);
        $instance->get('foo');
    }

}
