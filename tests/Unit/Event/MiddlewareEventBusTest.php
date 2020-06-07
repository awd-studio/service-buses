<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Event;

use AwdStudio\Bus\HandlerLocator;
use AwdStudio\Bus\MiddlewareChain;
use AwdStudio\Event\MiddlewareEventBus;
use AwdStudio\Event\EventBus;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \AwdStudio\Event\MiddlewareEventBus
 */
final class MiddlewareEventBusTest extends BusTestCase
{
    /** @var \AwdStudio\Event\MiddlewareEventBus */
    private $instance;

    /** @var \AwdStudio\Bus\HandlerLocator|\Prophecy\Prophecy\ObjectProphecy */
    private $handlersProphecy;

    /** @var \AwdStudio\Bus\MiddlewareChain|\Prophecy\Prophecy\ObjectProphecy */
    private $middlewareProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlersProphecy = $this->prophesize(HandlerLocator::class);
        $this->middlewareProphecy = $this->prophesize(MiddlewareChain::class);

        $this->instance = new MiddlewareEventBus($this->handlersProphecy->reveal(), $this->middlewareProphecy->reveal());
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
        $message = new class {
            /** @var bool */
            public $isChanged = false;
        };
        $handler = static function (object $message): void { $message->isChanged = true; };

        $this->handlersProphecy
            ->has(Argument::exact(\get_class($message)))
            ->willReturn(true);

        $this->handlersProphecy
            ->get(Argument::exact(\get_class($message)))
            ->willYield([$handler]);

        $this->middlewareProphecy
            ->chain(Argument::exact($message), Argument::exact($handler), Argument::type('array'))
            ->willReturn(static function () use ($message, $handler): void { $handler($message); });

        $this->instance->handle($message);

        $this->assertTrue($message->isChanged);
    }

    /**
     * @covers ::handle
     */
    public function testMustApplyAEachOfProvidedHandlers(): void
    {
        $message = new class {
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
            ->has(Argument::exact(\get_class($message)))
            ->willReturn(true);

        $this->handlersProphecy
            ->get(Argument::exact(\get_class($message)))
            ->willYield([$handler1, $handler2, $handler3]);

        $this->middlewareProphecy
            ->chain(Argument::exact($message), Argument::type('callable'), Argument::type('array'))
            ->willReturn(
                static function () use ($message, $handler1): void { $handler1($message); },
                static function () use ($message, $handler2): void { $handler2($message); },
                static function () use ($message, $handler3): void { $handler3($message); }
            );

        $this->instance->handle($message);

        $this->assertTrue($message->isChanged1);
        $this->assertTrue($message->isChanged2);
        $this->assertTrue($message->isChanged3);
    }
}
