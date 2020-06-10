<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus\Middleware;

use Prophecy\Argument;

/**
 * @coversDefaultClass \AwdStudio\Bus\Middleware\CallbackMiddlewareChain
 */
final class MiddlewareChainAdditionalParamsTest extends MiddlewareChainTestCase
{
    /**
     * @covers ::chain
     */
    public function testMustAcceptAdditionalParametersAndPassThemToAHandler(): void
    {
        $handler = static function (object $message, int $bar, string $baz): string {
            return $message->foo.$bar.$baz;
        };

        $message = new class() {
            public $foo = 'foo';
        };

        $this->handlersProphecy
            ->get(Argument::exact(\get_class($message)))
            ->willYield([]);

        $chain = $this->instance->chain($message, $handler, [42, 'baz']);

        $this->assertSame('foo42baz', $chain());
    }

    /**
     * @covers ::chain
     */
    public function testMustAcceptAdditionalParametersAndPassThemToMiddleware(): void
    {
        $handler = static function (object $message, int $bar, string $baz): string {
            return $message->foo.$bar.$baz;
        };

        $message = new class() {
            public $foo = 'foo';
        };

        $middleware1 = static function (object $message, callable $next, int $bar, string $baz): string {
            $result = $next();

            return $result.'qoo'.$bar;
        };

        $middleware2 = static function (object $message, callable $next, int $bar, string $baz): string {
            $result = $next();

            return $result.'qooooo'.$baz;
        };

        $this->handlersProphecy
            ->get(\get_class($message))
            ->willYield([$middleware1, $middleware2]);

        $chain = $this->instance->chain($message, $handler, [42, 'baz']);

        $this->assertSame('foo42bazqoo42qooooobaz', $chain());
    }
}
