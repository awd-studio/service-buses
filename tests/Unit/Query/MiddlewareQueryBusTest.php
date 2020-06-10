<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Query;

use AwdStudio\Bus\Exception\NoHandlerDefined;
use AwdStudio\Bus\HandlerLocator;
use AwdStudio\Bus\MiddlewareChain;
use AwdStudio\Query\MiddlewareQueryBus;
use AwdStudio\Query\QueryBus;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \AwdStudio\Query\MiddlewareQueryBus
 */
final class MiddlewareQueryBusTest extends BusTestCase
{
    /** @var \AwdStudio\Query\MiddlewareQueryBus */
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

        $this->instance = new MiddlewareQueryBus($this->handlersProphecy->reveal(), $this->middlewareProphecy->reveal());
    }

    /**
     * @covers ::__construct
     */
    public function testMustImplementAQueryBusInterface(): void
    {
        $this->assertInstanceOf(QueryBus::class, $this->instance);
    }

    /**
     * @covers ::handle
     */
    public function testMustApplyAHandler(): void
    {
        $message = new class() {
            /** @var string */
            public $copyMe = 'foo';
        };
        $handler = static function (object $message): string { return $message->copyMe; };

        $this->handlersProphecy
            ->has(Argument::exact($message))
            ->willReturn(true);

        $this->handlersProphecy
            ->get(Argument::exact(\get_class($message)))
            ->willYield([$handler]);

        $this->middlewareProphecy
            ->chain(Argument::exact($message), Argument::exact($handler), Argument::type('array'))
            ->willReturn(static function () use ($message, $handler): string { return $handler($message); });

        $result = $this->instance->handle($message);

        $this->assertSame('foo', $result);
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
        $handler1 = static function (object $message): int {
            $message->isChanged = true;

            return 42;
        };

        $handler2 = static function (object $message): int {
            $message->isCalled = true;

            return 69;
        };

        $this->handlersProphecy
            ->has(Argument::exact(\get_class($message)))
            ->willReturn(true);

        $this->handlersProphecy
            ->get(Argument::exact(\get_class($message)))
            ->willYield([$handler1, $handler2]);

        $this->middlewareProphecy
            ->chain(Argument::exact($message), Argument::type('callable'), Argument::type('array'))
            ->willReturn(
                static function () use ($message, $handler1): int { return $handler1($message); },
                static function () use ($message, $handler2): int { return $handler2($message); }
            );

        $result = $this->instance->handle($message);

        $this->assertSame(42, $result);
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
