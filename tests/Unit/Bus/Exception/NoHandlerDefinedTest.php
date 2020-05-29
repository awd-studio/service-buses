<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus\Exception;

use AwdStudio\Bus\Exception\NoHandlerDefined;
use AwdStudio\Tests\BusTestCase;

/**
 * @coversDefaultClass \AwdStudio\Bus\Exception\NoHandlerDefined
 */
class NoHandlerDefinedTest extends BusTestCase
{
    /** @var \AwdStudio\Bus\Exception\NoHandlerDefined */
    private $instance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new NoHandlerDefined(new \stdClass());
    }

    /**
     * @covers ::__construct
     */
    public function testMustImplementARuntimeException(): void
    {
        $this->assertInstanceOf(\RuntimeException::class, $this->instance);
    }

    /**
     * @covers ::__construct
     */
    public function testMustThrowAnExceptionWithACorrectMessage(): void
    {
        $this->expectExceptionMessage('No handlers for a message "stdClass"');

        throw $this->instance;
    }

    /**
     * @covers ::__construct
     */
    public function testMustThrowAnExceptionWithACorrectCode(): void
    {
        $this->expectExceptionCode(1);

        throw $this->instance;
    }
}
