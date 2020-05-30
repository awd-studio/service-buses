<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus\Middleware;

use Prophecy\Argument;

/**
 * @coversDefaultClass \AwdStudio\Bus\Middleware\MiddlewareChain
 */
final class MiddlewareChainHandlingTest extends MiddlewareChainTestCase
{
    /**
     * @covers ::buildChain
     */
    public function testMustInjectAHandlerAsAPartOfChainAndReturnItsResult(): void
    {
        $this->handlersProphecy
            ->get(Argument::exact(\stdClass::class))
            ->willYield([]);

        $handler = static function (object $message): int { return 42; };

        $chain = $this->instance->buildChain($handler, new \stdClass());

        $this->assertSame(42, $chain());
    }

    /**
     * @covers ::buildChain
     */
    public function testMustAllowToRunMiddlewareBeforeTheHandler(): void
    {
        $message = new class() {
            /** @var int */
            public $i = 0;
        };

        $middleware = static function (callable $next, object $message): int
        {
            ++$message->i;

            return $next();
        };

        $handler = static function (object $message) { return ++$message->i; };

        $this->handlersProphecy
            ->get(Argument::exact(\get_class($message)))
            ->willYield([$middleware]);

        $chain = $this->instance->buildChain($handler, $message);

        $this->assertSame(2, $chain());
        $this->assertSame(2, $message->i);
    }

    /**
     * @covers ::buildChain
     */
    public function testMustAllowToRunMiddlewareAfterTheHandler(): void
    {
        $message = new class() {
            /** @var int */
            public $i = 0;
        };

        $middleware = static function (callable $next, object $message): int
        {
            ++$message->i;
            $result = $next();
            ++$message->i;

            return $result;
        };

        $handler = static function (object $message) { return ++$message->i; };

        $this->handlersProphecy
            ->get(Argument::exact(\get_class($message)))
            ->willYield([$middleware]);

        $chain = $this->instance->buildChain($handler, $message);

        $this->assertSame(2, $chain());
        $this->assertSame(3, $message->i);
    }

    /**
     * @covers ::buildChain
     */
    public function testMustAllowMiddlewareToRewriteHandledResult(): void
    {
        $handler = static function (object $message): string
        {
            return 'foo';
        };

        $message = new \stdClass();

        $middleware = static function (callable $next, object $message): string
        {
            return 'bar';
        };

        $this->handlersProphecy
            ->get(Argument::exact(\get_class($message)))
            ->willYield([$middleware]);

        $chain = $this->instance->buildChain($handler, $message);

        $this->assertSame('bar', $chain());
    }
}
