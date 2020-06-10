<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Command;

use AwdStudio\Bus\Exception\NoHandlerDefined;
use AwdStudio\Bus\HandlerLocator;
use AwdStudio\Bus\MiddlewareChain;
use AwdStudio\Command\CommandBus;
use AwdStudio\Command\MiddlewareCommandBus;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \AwdStudio\Command\MiddlewareCommandBus
 */
final class MiddlewareCommandBusTest extends BusTestCase
{
    /** @var \AwdStudio\Command\MiddlewareCommandBus */
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

        $this->instance = new MiddlewareCommandBus($this->handlersProphecy->reveal(), $this->middlewareProphecy->reveal());
    }

    /**
     * @covers ::__construct
     */
    public function testMustImplementACommandBusInterface(): void
    {
        $this->assertInstanceOf(CommandBus::class, $this->instance);
    }

    /**
     * @covers ::handle
     */
    public function testMustApplyAHandler(): void
    {
        $message = new class() {
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
    public function testMustApplyTheOnlyFirstHandler(): void
    {
        $message = new class() {
            /** @var bool */
            public $isChanged = false;

            /** @var bool */
            public $isCalled = false;
        };
        $handler1 = static function (object $message): void { $message->isChanged = true; };
        $handler2 = static function (object $message): void { $message->isCalled = true; };

        $this->handlersProphecy
            ->has(Argument::exact(\get_class($message)))
            ->willReturn(true);

        $this->handlersProphecy
            ->get(Argument::exact(\get_class($message)))
            ->willYield([$handler1, $handler2]);

        $this->middlewareProphecy
            ->chain(Argument::exact($message), Argument::type('callable'), Argument::type('array'))
            ->willReturn(
                static function () use ($message, $handler1): void { $handler1($message); },
                static function () use ($message, $handler2): void { $handler2($message); }
            );

        $this->instance->handle($message);

        $this->assertTrue($message->isChanged);
        $this->assertFalse($message->isCalled);
    }

    /**
     * @covers ::handle
     */
    public function testMustThrowAnExceptionIfThereIsNoAHandler(): void
    {
        $this->handlersProphecy
            ->get(Argument::any())
            ->willYield([]);

        $this->expectException(NoHandlerDefined::class);

        $this->instance->handle(new \stdClass());
    }
}
