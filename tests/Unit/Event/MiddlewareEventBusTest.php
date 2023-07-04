<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Event;

use AwdStudio\Bus\HandlerLocator;
use AwdStudio\Event\EventBus;
use AwdStudio\Event\SimpleEventBus;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \AwdStudio\Event\SimpleEventBus
 */
final class MiddlewareEventBusTest extends BusTestCase
{
    private SimpleEventBus $instance;
    private HandlerLocator|ObjectProphecy $handlersProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlersProphecy = $this->prophesize(HandlerLocator::class);

        $this->instance = new SimpleEventBus($this->handlersProphecy->reveal());
    }

    /**
     * @covers ::__construct
     */
    public function testMustImplementACommandBusInterface(): void
    {
        $this->assertInstanceOf(EventBus::class, $this->instance);
    }

    /**
     * @covers ::handle
     */
    public function testMustDoNothingIfThereIsNoHandlers(): void
    {
        $this->handlersProphecy
            ->get(Argument::exact(\stdClass::class))
            ->willYield([])
            ->shouldBeCalledOnce();

        $this->instance->handle(new \stdClass());
    }

    /**
     * @covers ::handle
     */
    public function testMustApplyASingleHandler(): void
    {
        $message = new class() {
            public bool $isChanged = false;
        };
        $handler = static function (object $message): void { $message->isChanged = true; };

        $this->handlersProphecy
            ->get(Argument::exact($message::class))
            ->willYield([$handler]);

        $this->instance->handle($message);

        $this->assertTrue($message->isChanged);
    }

    /**
     * @covers ::handle
     */
    public function testMustApplyAEachOfProvidedHandlers(): void
    {
        $message = new class() {
            /** @var bool */
            public $isChanged1 = false;

            /** @var bool */
            public $isChanged2 = false;

            /** @var bool */
            public $isChanged3 = false;
        };

        $handler1 = static function (object $message): void { $message->isChanged1 = true; };
        $handler2 = static function (object $message): void { $message->isChanged2 = true; };
        $handler3 = static function (object $message): void { $message->isChanged3 = true; };

        $this->handlersProphecy
            ->get(Argument::exact($message::class))
            ->willYield([$handler1, $handler2, $handler3]);

        $this->instance->handle($message);

        $this->assertTrue($message->isChanged1);
        $this->assertTrue($message->isChanged2);
        $this->assertTrue($message->isChanged3);
    }
}
