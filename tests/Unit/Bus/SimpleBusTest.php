<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus;

use AwdStudio\Bus\HandlerLocator;
use AwdStudio\Bus\SimpleBus;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \AwdStudio\Bus\SimpleBus
 */
final class SimpleBusTest extends BusTestCase
{
    private SimpleBus $instance;
    private HandlerLocator|ObjectProphecy $handlerLocatorProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlerLocatorProphecy = $this->prophesize(HandlerLocator::class);

        $this->instance = new class($this->handlerLocatorProphecy->reveal()) extends SimpleBus {
            /** @return iterable<callable> */
            public function test(object $message): iterable
            {
                yield from $this->handleMessage($message);
            }
        };
    }

    /**
     * @covers ::__construct
     */
    public function testMustProvideAPublicConstructor(): void
    {
        $this->assertNotNull($this->instance);
    }

    /**
     * @covers ::handleMessage
     */
    public function testMustApplyEachOfHandlersDuringTheHandling(): void
    {
        $message = new class() {
            public $h1 = 0;
            public $h2 = 0;
            public $h3 = 0;
        };

        $handler1 = static function (object $message): void { $message->h1 = 1; };
        $handler2 = static function (object $message): void { $message->h2 = 42; };
        $handler3 = static function (object $message): void { $message->h3 = 1024; };

        $this->handlerLocatorProphecy
            ->get(Argument::exact($message::class))
            ->willYield([$handler1, $handler2, $handler3]);

        \iterator_to_array($this->instance->test($message));

        $this->assertSame(1, $message->h1);
        $this->assertSame(42, $message->h2);
        $this->assertSame(1024, $message->h3);
    }
}
