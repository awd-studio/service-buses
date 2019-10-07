<?php

namespace AwdStudio\Tests\ServiceBuses\Implementation\Handling;

use AwdStudio\ServiceBuses\Exception\HandlerNotDefined;
use AwdStudio\ServiceBuses\Implementation\Handling\InMemoryHandlerLocator;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \AwdStudio\ServiceBuses\Implementation\Handling\InMemoryHandlerLocator
 */
class InMemoryHandlerLocatorTest extends TestCase
{

    /**
     * @covers ::__construct
     * @covers ::get
     */
    public function testGet()
    {
        $handler = function ($command) { };

        $instance = new InMemoryHandlerLocator(['foo' => [$handler]]);

        foreach ($instance->get('foo') as $item) {
            $this->assertSame($handler, $item);
        }
    }

    /**
     * @covers ::__construct
     * @covers ::get
     */
    public function testGetNoHandlers()
    {
        $instance = new InMemoryHandlerLocator(['foo' => []]);

        $this->expectException(HandlerNotDefined::class);
        $instance->get('foo');
    }

}
