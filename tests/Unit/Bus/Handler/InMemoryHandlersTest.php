<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus\Handler;

use AwdStudio\Bus\Handler\InMemoryHandlers;
use AwdStudio\Bus\Handlers;
use AwdStudio\Tests\BusTestCase;

/**
 * @coversDefaultClass \AwdStudio\Bus\Handler\InMemoryHandlers
 */
final class InMemoryHandlersTest extends BusTestCase
{
    /** @var \AwdStudio\Bus\Handler\InMemoryHandlers */
    private $instance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new InMemoryHandlers();
    }

    /**
     * @covers ::__construct
     */
    public function testMustImplementAHandlers(): void
    {
        $this->assertInstanceOf(Handlers::class, $this->instance);
    }

    /**
     * @covers ::add
     */
    public function testMustProvideAnInterfaceToAppendHandlers(): void
    {
        $handler = static function (object $message) { };

        $this->instance->add(\stdClass::class, $handler);

        $this->assertContains($handler, $this->instance->get(\stdClass::class));
    }

    /**
     * @covers ::has
     */
    public function testMustReturnFalseIfThereIsNoAssignedHandlersForAMessage(): void
    {
        $this->assertFalse($this->instance->has(\stdClass::class));
    }

    /**
     * @covers ::has
     */
    public function testMustReturnTrueIfThereIsASingleHandlersForAMessage(): void
    {
        $this->instance->add(\stdClass::class, static function (object $message) { });

        $this->assertTrue($this->instance->has(\stdClass::class));
    }

    /**
     * @covers ::has
     */
    public function testMustReturnTrueIfThereAreMultipleAssignedHandlersForAMessage(): void
    {
        $this->instance->add(\stdClass::class, static function (object $message) { });
        $this->instance->add(\stdClass::class, static function (object $message) { });
        $this->instance->add(\stdClass::class, static function (object $message) { });

        $this->assertTrue($this->instance->has(\stdClass::class));
    }

    /**
     * @covers ::get
     */
    public function testMustReturnAListOfHandlersForAMessage(): void
    {
        $handler1 = static function (object $message) { };
        $handler2 = static function (object $message) { };
        $handler3 = static function (object $message) { };

        $this->instance->add(\stdClass::class, $handler1);
        $this->instance->add(\stdClass::class, $handler2);
        $this->instance->add(\stdClass::class, $handler3);

        $handlers = $this->instance->get(\stdClass::class);

        $this->assertContains($handler1, $handlers);
        $this->assertContains($handler2, $handlers);
        $this->assertContains($handler3, $handlers);
    }

    /**
     * @covers ::get
     */
    public function testMustReturnAnEmptyIteratorWhenThereIsNoHandlers(): void
    {
        $handlers = $this->instance->get(\stdClass::class);

        $this->assertEmpty($handlers instanceof \Traversable ? \iterator_to_array($handlers) : $handlers);
    }
}
