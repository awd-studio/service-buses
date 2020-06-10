<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus\Reader;

use AwdStudio\Bus\Exception\ParsingException;
use AwdStudio\Bus\Reader\MessageIdResolver;
use AwdStudio\Bus\Reader\ReflectionMessageIdReader;
use AwdStudio\Tests\BusTestCase;

function foo(\stdClass $message): void
{
}

/**
 * @coversDefaultClass \AwdStudio\Bus\Reader\ReflectionMessageIdReader
 */
final class ReflectionMessageIdReaderTest extends BusTestCase
{
    /** @var \AwdStudio\Bus\Reader\ReflectionMessageIdReader */
    private $instance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new ReflectionMessageIdReader();
    }

    /**
     * @coversNothing
     */
    public function testMustImplementAMessageIdResolver(): void
    {
        $this->assertInstanceOf(MessageIdResolver::class, $this->instance);
    }

    /**
     * @covers ::read
     */
    public function testMustReadACorrectMessageFromACallback(): void
    {
        $reflection = new \ReflectionFunction(__NAMESPACE__.'\\foo');

        $this->assertSame(\stdClass::class, $this->instance->read($reflection));
    }

    /**
     * @covers ::read
     */
    public function testMustReadACorrectMessageFromAClosure(): void
    {
        $callback = static function (\stdClass $message): void { };
        $reflection = new \ReflectionFunction(\Closure::fromCallable($callback));

        $this->assertSame(\stdClass::class, $this->instance->read($reflection));
    }

    /**
     * @covers ::read
     */
    public function testMustReadACorrectMessageFromACallableClass(): void
    {
        $callback = new class() {
            public function __invoke(\stdClass $message): void
            {
            }
        };
        $reflection = (new \ReflectionClass($callback))->getMethod('__invoke');

        $this->assertSame(\stdClass::class, $this->instance->read($reflection));
    }

    /**
     * @covers ::read
     */
    public function testMustThrowAnExceptionIfThereIsNoAParameter(): void
    {
        $callback = static function (): void { };
        $reflection = new \ReflectionFunction(\Closure::fromCallable($callback));

        $this->expectException(ParsingException::class);

        $this->instance->read($reflection);
    }

    /**
     * @covers ::read
     */
    public function testMustThrowAnExceptionIfFirstParameterHasNoType(): void
    {
        $callback = static function ($message): void { };
        $reflection = new \ReflectionFunction(\Closure::fromCallable($callback));

        $this->expectException(ParsingException::class);

        $this->instance->read($reflection);
    }

    /**
     * @covers ::read
     */
    public function testMustThrowAnExceptionIfFirstParameterIsNotAClass(): void
    {
        $callback = static function (object $message): void { };
        $reflection = new \ReflectionFunction(\Closure::fromCallable($callback));

        $this->expectException(ParsingException::class);

        $this->instance->read($reflection);
    }
}
