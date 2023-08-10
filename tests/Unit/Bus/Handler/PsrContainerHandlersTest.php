<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus\Handler;

use AwdStudio\Bus\Exception\InvalidHandler;
use AwdStudio\Bus\Handler\PsrContainerClassHandlerRegistry;
use AwdStudio\Bus\HandlerLocator;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;

class Foo
{
}

class FooHandler
{
    public function __invoke(): void
    {
    }
}

class FooHandler1
{
    public function __invoke(): void
    {
    }
}

class FooHandler2
{
    public function __invoke(): void
    {
    }
}

class FooHandler3
{
    public static $isCalled = false;

    public function handle(): void
    {
        self::$isCalled = true;
    }
}

/**
 * @coversDefaultClass \AwdStudio\Bus\Handler\PsrContainerClassHandlerRegistry
 */
final class PsrContainerHandlersTest extends BusTestCase
{
    /** @var \AwdStudio\Bus\Handler\PsrContainerClassHandlerRegistry */
    private $instance;

    /** @var \Psr\Container\ContainerInterface|\Prophecy\Prophecy\ObjectProphecy */
    private $serviceLocator;

    /** @var \AwdStudio\Bus\HandlerLocator|\Prophecy\Prophecy\ObjectProphecy */
    private $dynamicHandlers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceLocator = $this->prophesize(ContainerInterface::class);
        $this->dynamicHandlers = $this->prophesize(HandlerLocator::class);

        $this->instance = new PsrContainerClassHandlerRegistry(
            $this->serviceLocator->reveal(),
            $this->dynamicHandlers->reveal()
        );
    }

    /**
     * @covers ::__construct
     */
    public function testMustImplementAHandlers(): void
    {
        $this->assertInstanceOf(HandlerLocator::class, $this->instance);
    }

    /**
     * @covers ::__construct
     */
    public function testMustAllowToInstantiateWithoutExternalHandlers(): void
    {
        $this->assertNotNull(new PsrContainerClassHandlerRegistry($this->serviceLocator->reveal()));
    }

    /**
     * @covers ::register
     */
    public function testMustCheckAServiceWhenTriesToRegisterIt(): void
    {
        $this->serviceLocator
            ->has(Argument::exact(FooHandler::class))
            ->willReturn(true)
            ->shouldBeCalledOnce();

        $this->instance->register(FooCallback::class, FooHandler::class);
    }

    /**
     * @covers ::register
     */
    public function testMustThrowAnExceptionIfAHandlerIsNotInTheServiceLocator(): void
    {
        $this->serviceLocator
            ->has(Argument::exact(FooHandler::class))
            ->willReturn(false);

        $this->expectException(InvalidHandler::class);

        $this->instance->register(FooCallback::class, FooHandler::class);
    }

    /**
     * @covers ::add
     */
    public function testMustCallAnExternalHandlersToAddADynamicHandler(): void
    {
        $dynamicHandler = static function (): void { return; };

        $this->dynamicHandlers
            ->add(Argument::exact(\stdClass::class), Argument::exact($dynamicHandler))
            ->shouldBeCalledOnce();

        $this->instance->add(\stdClass::class, $dynamicHandler);
    }

    /**
     * @covers ::has
     */
    public function testMustCheckAnExternalHandlersWhenLooksForAHandler(): void
    {
        $this->dynamicHandlers
            ->has(Argument::exact(FooCallback::class))
            ->willReturn(false)
            ->shouldBeCalledOnce();

        $this->instance->has(FooCallback::class);
    }

    /**
     * @covers ::has
     */
    public function testMustReturnTrueIfAHandlerIsInExternalHandlers(): void
    {
        $this->dynamicHandlers
            ->has(Argument::exact(FooCallback::class))
            ->willReturn(true);

        $this->assertTrue($this->instance->has(FooCallback::class));
    }

    /**
     * @covers ::has
     */
    public function testMustReturnTrueIfAHandlerRegisteredInTheContainerHandlers(): void
    {
        $this->dynamicHandlers
            ->has(Argument::exact(FooCallback::class))
            ->willReturn(false);

        $this->serviceLocator
            ->has(Argument::exact(FooHandler::class))
            ->willReturn(true);

        $this->instance->register(FooCallback::class, FooHandler::class);

        $this->assertTrue($this->instance->has(FooCallback::class));
    }

    /**
     * @covers ::has
     */
    public function testMustReturnFalseIfThereAreNoHandlersNorInDynamicHandlersNotInRegisteredOnes(): void
    {
        $this->dynamicHandlers
            ->has(Argument::exact(FooCallback::class))
            ->willReturn(false);

        $this->assertFalse($this->instance->has(FooCallback::class));
    }

    /**
     * @covers ::get
     */
    public function testMustYieldAHandlerFromDynamicHandlersIfItHasSome(): void
    {
        $dynamicHandler = static function (): void { return; };

        $this->dynamicHandlers
            ->has(Argument::exact(FooCallback::class))
            ->willReturn(true);

        $this->dynamicHandlers
            ->get(Argument::exact(FooCallback::class))
            ->willYield([$dynamicHandler]);

        $this->assertContains($dynamicHandler, $this->instance->get(FooCallback::class));
    }

    /**
     * @covers ::get
     */
    public function testMustYieldAHandlerFromContainerIfItRegistered(): void
    {
        $dynamicHandler = static function (): void { return; };

        $this->dynamicHandlers
            ->has(Argument::exact(FooCallback::class))
            ->willReturn(false);

        $this->serviceLocator
            ->has(Argument::exact(FooHandler::class))
            ->willReturn(true);

        $this->serviceLocator
            ->get(Argument::exact(FooHandler::class))
            ->willReturn($dynamicHandler);

        $this->instance->register(FooCallback::class, FooHandler::class);

        $this->assertContains($dynamicHandler, $this->instance->get(FooCallback::class));
    }

    /**
     * @covers ::get
     */
    public function testMustYieldAllHandlersFromBothAndDynamicHandlersAndTheContainer(): void
    {
        $dynamicHandler1 = static function (): void { return; };
        $dynamicHandler2 = static function (): void { return; };
        $dynamicHandler3 = static function (): void { return; };

        $this->dynamicHandlers
            ->has(Argument::exact(FooCallback::class))
            ->willReturn(true);

        $this->dynamicHandlers
            ->get(Argument::exact(FooCallback::class))
            ->willYield([$dynamicHandler3]);

        $this->serviceLocator
            ->get(Argument::any())
            ->willReturn($dynamicHandler1, $dynamicHandler2);

        $this->serviceLocator
            ->has(Argument::any())
            ->willReturn(true);

        $this->instance->register(FooCallback::class, FooHandler1::class);
        $this->instance->register(FooCallback::class, FooHandler2::class);

        $result = [];
        foreach ($this->instance->get(FooCallback::class) as $handler) {
            $result[] = $handler;
        }

        $this->assertContains($dynamicHandler1, $result);
        $this->assertContains($dynamicHandler2, $result);
        $this->assertContains($dynamicHandler3, $result);
    }

    /**
     * @covers ::get
     */
    public function testMustReturnACallableArrayAsAnObjectAndRegisteredMethod(): void
    {
        $this->dynamicHandlers
            ->has(Argument::any())
            ->willReturn(false);

        $this->serviceLocator
            ->has(Argument::exact(FooHandler3::class))
            ->willReturn(true);

        $this->serviceLocator
            ->get(Argument::exact(FooHandler3::class))
            ->willReturn(new FooHandler3());

        $this->instance->register(FooCallback::class, FooHandler3::class, 'handle');
        $this->instance->get(FooCallback::class)->current()();

        $this->assertTrue(FooHandler3::$isCalled);
    }
}
