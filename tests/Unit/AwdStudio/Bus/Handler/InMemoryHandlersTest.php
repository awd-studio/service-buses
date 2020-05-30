<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\AwdStudio\Bus\Handler;

use AwdStudio\Bus\Exception\NoHandlerDefined;
use AwdStudio\Bus\Handler\InMemoryHandlers;
use AwdStudio\Bus\Handlers;
use AwdStudio\Tests\BusTestCase;

/**
 * @coversDefaultClass \AwdStudio\Bus\Handler\InMemoryHandlers
 */
class InMemoryHandlersTest extends BusTestCase
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

        $this->assertContains($handler, $this->instance->get(new \stdClass()));
    }

    /**
     * @covers ::has
     */
    public function testMustReturnFalseIfThereIsNoAssignedHandlersForAMessage(): void
    {
        $this->assertFalse($this->instance->has(new \stdClass()));
    }

    /**
     * @covers ::has
     */
    public function testMustReturnTrueIfThereIsASingleHandlersForAMessage(): void
    {
        $this->instance->add(\stdClass::class, static function (object $message) { });

        $this->assertTrue($this->instance->has(new \stdClass()));
    }

    /**
     * @covers ::has
     */
    public function testMustReturnTrueIfThereAreMultipleAssignedHandlersForAMessage(): void
    {
        $this->instance->add(\stdClass::class, static function (object $message) { });
        $this->instance->add(\stdClass::class, static function (object $message) { });
        $this->instance->add(\stdClass::class, static function (object $message) { });

        $this->assertTrue($this->instance->has(new \stdClass()));
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

        $handlers = \iterator_to_array($this->instance->get(new \stdClass()));

        $this->assertContains($handler1, $handlers);
        $this->assertContains($handler2, $handlers);
        $this->assertContains($handler3, $handlers);
    }

    /**
     * @covers ::get
     */
    public function testMustThrowAnExceptionIfTriesToGetAHandlerThatNotInTheList(): void
    {
        $this->expectException(NoHandlerDefined::class);

        $this->instance->get(new \stdClass());
    }

    /**
     * @covers ::export
     */
    public function testMustProvideAnInterfaceToExportAllHandlers(): void
    {
        $handler1 = static function (object $message) { };
        $handler2 = static function (object $message) { };
        $handler3 = static function (object $message) { };

        $this->instance->add(\stdClass::class, $handler1);
        $this->instance->add(\stdClass::class, $handler2);
        $this->instance->add(\stdClass::class, $handler3);

        $this->assertEquals([\stdClass::class => [$handler1, $handler2, $handler3,]], $this->instance->export());
    }
}
