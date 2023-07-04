<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Command;

use AwdStudio\Bus\Exception\NoHandlerDefined;
use AwdStudio\Bus\HandlerLocator;
use AwdStudio\Command\CommandBus;
use AwdStudio\Command\SimpleCommandBus;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \AwdStudio\Command\SimpleCommandBus
 */
final class MiddlewareCommandBusTest extends BusTestCase
{
    private SimpleCommandBus $instance;
    private HandlerLocator|ObjectProphecy $handlersProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlersProphecy = $this->prophesize(HandlerLocator::class);

        $this->instance = new SimpleCommandBus($this->handlersProphecy->reveal());
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
            ->has(Argument::exact($message::class))
            ->willReturn(true);

        $this->handlersProphecy
            ->get(Argument::exact($message::class))
            ->willYield([$handler]);

        $this->instance->handle($message);

        $this->assertTrue($message->isChanged);
    }

    /**
     * @covers ::handle
     */
    public function testMustApplyTheOnlyFirstHandler(): void
    {
        $message = new class() {
            public bool $isChanged = false;
            public bool $isCalled = false;
        };
        $handler1 = static function (object $message): void { $message->isChanged = true; };
        $handler2 = static function (object $message): void { $message->isCalled = true; };

        $this->handlersProphecy
            ->has(Argument::exact($message::class))
            ->willReturn(true);

        $this->handlersProphecy
            ->get(Argument::exact($message::class))
            ->willYield([$handler1, $handler2]);

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
