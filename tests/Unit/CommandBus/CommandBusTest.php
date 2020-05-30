<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\CommandBus;

use AwdStudio\Bus\Exception\NoHandlerDefined;
use AwdStudio\Bus\Handlers;
use AwdStudio\Bus\Middleware;
use AwdStudio\CommandBus\CommandBus;
use AwdStudio\CommandBus\CommandBusInterface;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \AwdStudio\CommandBus\CommandBus
 */
class CommandBusTest extends BusTestCase
{
    /** @var \AwdStudio\CommandBus\CommandBus */
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

        $this->instance = new CommandBus($this->handlersProphecy->reveal(), $this->middlewareProphecy->reveal());
    }

    /**
     * @covers ::__construct
     */
    public function testMustImplementACommandBusInterface(): void
    {
        $this->assertInstanceOf(CommandBusInterface::class, $this->instance);
    }

    /**
     * @covers ::handle
     */
    public function testMustApplyAHandler(): void
    {
        $message = new class {
            /** @var bool */
            public $isChanged = false;
        };
        $handler = static function (object $message): void { $message->isChanged = true; };

        $this->handlersProphecy
            ->get(Argument::exact($message))
            ->willYield([$handler]);

        $this->middlewareProphecy
            ->buildChain(Argument::exact($message), Argument::exact($handler))
            ->willReturn(static function () use ($message, $handler): void { $handler($message); });

        $this->instance->handle($message);

        $this->assertTrue($message->isChanged);
    }

    /**
     * @covers ::handle
     */
    public function testMustApplyTheOnlyFirstHandler(): void
    {
        $message = new class {
            /** @var bool */
            public $isChanged = false;

            /** @var bool */
            public $isCalled = false;
        };
        $handler1 = static function (object $message): void { $message->isChanged = true; };
        $handler2 = static function (object $message): void { $message->isCalled = true; };

        $this->handlersProphecy
            ->get(Argument::exact($message))
            ->willYield([$handler1, $handler2]);

        $this->middlewareProphecy
            ->buildChain(Argument::exact($message), Argument::exact($handler1))
            ->willReturn(
                static function () use ($message, $handler1): void { $handler1($message); },
                static function () use ($message, $handler2): void { $handler2($message); }
            )
            ->shouldBeCalledOnce();

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
            ->willThrow(NoHandlerDefined::class);

        $this->expectException(NoHandlerDefined::class);

        $this->instance->handle(new \stdClass());
    }
}
