<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Query;

use AwdStudio\Bus\Exception\NoHandlerDefined;
use AwdStudio\Bus\HandlerLocator;
use AwdStudio\Query\SimpleQueryBus;
use AwdStudio\Query\QueryBus;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \AwdStudio\Query\SimpleQueryBus
 */
final class MiddlewareQueryBusTest extends BusTestCase
{
    private SimpleQueryBus $instance;
    private HandlerLocator|ObjectProphecy $handlersProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlersProphecy = $this->prophesize(HandlerLocator::class);

        $this->instance = new SimpleQueryBus($this->handlersProphecy->reveal());
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
            ->get(Argument::exact($message::class))
            ->willYield([$handler]);

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
            ->has(Argument::exact($message::class))
            ->willReturn(true);

        $this->handlersProphecy
            ->get(Argument::exact($message::class))
            ->willYield([$handler1, $handler2]);

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
