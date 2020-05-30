<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus;

use AwdStudio\Bus\MiddlewareBus;
use AwdStudio\Bus\Handlers;
use AwdStudio\Bus\Middleware;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \AwdStudio\Bus\MiddlewareBus
 */
final class MiddlewareBusTest extends BusTestCase
{
    /** @var \AwdStudio\Bus\MiddlewareBus */
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

        $this->instance = new class(
            $this->handlersProphecy->reveal(),
            $this->middlewareProphecy->reveal()
        ) extends MiddlewareBus {
            /**
             * @param object $message
             * @param mixed  ...$extra
             *
             * @return iterable<callable>
             */
            public function test(object $message, ...$extra): iterable
            {
                yield from $this->chain($message, ...$extra);
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
     * @covers ::chain
     */
    public function testMustBuildAChainWithAHandler(): void
    {
        $message = new \stdClass();
        $handler = static function (\stdClass $message): int { return 42; };

        $this->handlersProphecy
            ->get(Argument::exact(\stdClass::class))
            ->willYield([$handler]);

        $this->middlewareProphecy
            ->buildChain(Argument::type('callable'), Argument::exact($message), Argument::any())
            ->willReturn(static function () use ($message, $handler): int { return $handler($message); });

        $firstResult = $this->instance->test($message);

        $this->assertSame(42, ($firstResult->current())());
    }

    /**
     * @covers ::chain
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

        $this->handlersProphecy
            ->has(Argument::exact(\get_class($message)))
            ->willReturn(true);

        $this->handlersProphecy
            ->get(Argument::exact(\get_class($message)))
            ->willYield([$handler1, $handler2, $handler3]);

        $this->middlewareProphecy
            ->buildChain(Argument::type('callable'), Argument::exact($message), Argument::any())
            ->willReturn(
                static function () use ($message, $handler1): void { $handler1($message); },
                static function () use ($message, $handler2): void { $handler2($message); },
                static function () use ($message, $handler3): void { $handler3($message); }
            );

        foreach ($this->instance->test($message) as $chain) {
            $chain();
        }

        $this->assertSame(1, $message->h1);
        $this->assertSame(42, $message->h2);
        $this->assertSame(1024, $message->h3);
    }
}
