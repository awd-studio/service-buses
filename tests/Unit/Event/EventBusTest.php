<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Event;

use AwdStudio\Bus\Handlers;
use AwdStudio\Bus\Middleware;
use AwdStudio\Event\EventBus;
use AwdStudio\Event\IEventBus;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \AwdStudio\Event\EventBus
 */
final class EventBusTest extends BusTestCase
{
    /** @var \AwdStudio\Event\EventBus */
    private $instance;

    /** @var \AwdStudio\Bus\Handlers|\Prophecy\Prophecy\ObjectProphecy */
    private $handlersProphecy;

    /** @var \AwdStudio\Bus\Middleware|\Prophecy\Prophecy\ObjectProphecy */
    private $middlewareProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlersProphecy = $this->prophesize(Handlers::class);
        $this->middlewareProphecy = $this->prophesize(Middleware::class);

        $this->instance = new EventBus($this->handlersProphecy->reveal(), $this->middlewareProphecy->reveal());
    }

    /**
     * @covers ::__construct
     */
    public function testMustImplementACommandBusInterface(): void
    {
        $this->assertInstanceOf(IEventBus::class, $this->instance);
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
            ->buildChain(Argument::exact($handler), Argument::exact($message), Argument::type('array'))
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
            ->buildChain(Argument::type('callable'), Argument::exact($message), Argument::type('array'))
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
