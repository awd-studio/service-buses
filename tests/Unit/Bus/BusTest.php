<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus;

use AwdStudio\Bus\Bus;
use AwdStudio\Bus\Handlers;
use AwdStudio\Bus\Middleware;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \AwdStudio\Bus\Bus
 */
final class BusTest extends BusTestCase
{
    /** @var \AwdStudio\Bus\Bus */
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

        $this->instance = new class($this->handlersProphecy->reveal(), $this->middlewareProphecy->reveal()) extends Bus {
            /**
             * @param object $message
             *
             * @return mixed|null
             */
            public function test(object $message)/*: \Generator*/
            {
                $result = null;
                foreach ($this->doHandling($message) as $time) {
                    $result = $time;
                }

                return $result;
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
     * @covers ::doHandling
     */
    public function testMustReturnAResultFromAHandler(): void
    {
        $message = new \stdClass();
        $handler = static function (\stdClass $message): int { return 42; };

        $this->handlersProphecy
            ->get(Argument::exact($message))
            ->willYield([$handler])
            ->shouldBeCalledOnce();

        $this->middlewareProphecy
            ->buildChain(Argument::exact($message), Argument::exact($handler))
            ->willReturn(static function () use ($message, $handler): int { return $handler($message); })
            ->shouldBeCalledOnce();

        $firstResult = $this->instance->test($message);

        $this->assertSame(42, $firstResult);
    }

    /**
     * @covers ::doHandling
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
            ->get(Argument::exact($message))
            ->willYield([$handler1, $handler2, $handler3]);

        $this->middlewareProphecy
            ->buildChain(Argument::exact($message), Argument::type('callable'))
            ->willReturn(
                static function () use ($message, $handler1): void { $handler1($message); },
                static function () use ($message, $handler2): void { $handler2($message); },
                static function () use ($message, $handler3): void { $handler3($message); }
            );

        $this->instance->test($message);

        $this->assertSame(1, $message->h1);
        $this->assertSame(42, $message->h2);
        $this->assertSame(1024, $message->h3);
    }
}
