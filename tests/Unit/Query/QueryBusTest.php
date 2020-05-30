<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Query;

use AwdStudio\Bus\Exception\NoHandlerDefined;
use AwdStudio\Bus\Handlers;
use AwdStudio\Bus\Middleware;
use AwdStudio\Query\IQueryBus;
use AwdStudio\Query\QueryBus;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \AwdStudio\Query\QueryBus
 */
class QueryBusTest extends BusTestCase
{
    /** @var \AwdStudio\Query\QueryBus */
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

        $this->instance = new QueryBus($this->handlersProphecy->reveal(), $this->middlewareProphecy->reveal());
    }

    /**
     * @covers ::__construct
     */
    public function testMustImplementAQueryBusInterface(): void
    {
        $this->assertInstanceOf(IQueryBus::class, $this->instance);
    }

    /**
     * @covers ::handle
     */
    public function testMustApplyAHandler(): void
    {
        $message = new class {
            /** @var string */
            public $copyMe = 'foo';
        };
        $handler = static function (object $message): string { return $message->copyMe; };

        $this->handlersProphecy
            ->get(Argument::exact($message))
            ->willYield([$handler]);

        $this->middlewareProphecy
            ->buildChain(Argument::exact($message), Argument::exact($handler))
            ->willReturn(static function () use ($message, $handler): string { return $handler($message); });

        $result = $this->instance->handle($message);

        $this->assertSame('foo', $result);
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
        $handler1 = static function (object $message): int
        {
            $message->isChanged = true;

            return 42;
        };

        $handler2 = static function (object $message): int
        {
            $message->isCalled = true;

            return 69;
        };

        $this->handlersProphecy
            ->get(Argument::exact($message))
            ->willYield([$handler1, $handler2]);

        $this->middlewareProphecy
            ->buildChain(Argument::exact($message), Argument::exact($handler1))
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
    public
    function testMustThrowAnExceptionIfThereIsNoAHandler(): void
    {
        $this->handlersProphecy
            ->get(Argument::any())
            ->willThrow(NoHandlerDefined::class);

        $this->expectException(NoHandlerDefined::class);

        $this->instance->handle(new \stdClass());
    }
}
