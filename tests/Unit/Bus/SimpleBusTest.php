<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus;

use AwdStudio\Bus\HandlerLocator;
use AwdStudio\Bus\SimpleBus;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \AwdStudio\Bus\SimpleBus
 */
final class SimpleBusTest extends BusTestCase
{
    /** @var \AwdStudio\Bus\SimpleBus */
    private $instance;

    /** @var \AwdStudio\Bus\HandlerLocator|\Prophecy\Prophecy\ObjectProphecy */
    private $handlersProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlersProphecy = $this->prophesize(HandlerLocator::class);

        $this->instance = new class($this->handlersProphecy->reveal()) extends SimpleBus {
            /**
             * @param object $message
             * @param mixed  ...$extraParams
             *
             * @return \Iterator
             */
            public function test(object $message, ...$extraParams): \Iterator
            {
                foreach ($this->handleAll($message, ...$extraParams) as $result) {
                    yield $result;
                }
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
     * @covers ::handleAll
     */
    public function testMustDoNothingIfThereIsNoHandlers(): void
    {
        $this->handlersProphecy
            ->get(Argument::exact(\stdClass::class))
            ->willYield([])
            ->shouldBeCalledOnce();

        \iterator_to_array($this->instance->test(new \stdClass()));
    }

    /**
     * @covers ::handleAll
     */
    public function testMustCallAllHandlers(): void
    {
        $h1 = static function (\stdClass $message): int { return 1; };
        $h2 = static function (\stdClass $message): int { return 2; };
        $h3 = static function (\stdClass $message): int { return 3; };

        $this->handlersProphecy
            ->get(Argument::exact(\stdClass::class))
            ->willYield([$h1, $h2, $h3]);

        $results = \iterator_to_array($this->instance->test(new \stdClass()));

        $this->assertCount(3, $results);
        $this->assertContains(1, $results);
        $this->assertContains(2, $results);
        $this->assertContains(3, $results);
    }

    /**
     * @covers ::handleAll
     */
    public function testMustCallTheOnlyFirstHandlerIfNextAreBraked(): void
    {
        $a1 = false;
        $a2 = false;
        $a3 = false;

        $h1 = static function (\stdClass $message) use (&$a1): void { $a1 = true; };
        $h2 = static function (\stdClass $message) use (&$a2): void { $a2 = true; };
        $h3 = static function (\stdClass $message) use (&$a3): void { $a3 = true; };

        $this->handlersProphecy
            ->get(Argument::exact(\stdClass::class))
            ->willYield([$h1, $h2, $h3]);

        foreach ($this->instance->test(new \stdClass()) as $result) {
            unset($result);

            break;
        }

        $this->assertTrue($a1);
        $this->assertFalse($a2);
        $this->assertFalse($a3);
    }

    /**
     * @covers ::handleAll
     */
    public function testMustCallHandlersWithExactSameMessage(): void
    {
        $message = new class() {
            public $i = 0;
        };

        $h1 = static function (object $message): void { ++$message->i; };
        $h2 = static function (object $message): void { ++$message->i; };
        $h3 = static function (object $message): void { ++$message->i; };

        $this->handlersProphecy
            ->get(Argument::any())
            ->willYield([$h1, $h2, $h3]);

        \iterator_to_array($this->instance->test($message));

        $this->assertSame(3, $message->i);
    }

    /**
     * @covers ::handleAll
     */
    public function testMustPassAllAdditionalParametersToAllHandlers(): void
    {
        $extra1 = null;
        $extra2 = null;
        $extra3 = null;

        $h1 = static function (\stdClass $message, int $e1) use (&$extra1): void {
            $extra1 = $e1;
        };

        $h2 = static function (\stdClass $message, int $e1, string $e2) use (&$extra2): void {
            $extra2 = $e2;
        };

        $h3 = static function (\stdClass $message, int $e1, string $e2, \stdClass $e3) use (&$extra3): void {
            $extra3 = $e3->bar;
        };

        $this->handlersProphecy
            ->get(Argument::any())
            ->willYield([$h1, $h2, $h3]);

        $e3 = new \stdClass();
        $e3->bar = 'baz';

        \iterator_to_array($this->instance->test(new \stdClass(), 42, 'foo', $e3));

        $this->assertSame(42, $extra1);
        $this->assertSame('foo', $extra2);
        $this->assertSame('baz', $extra3);
    }
}
