<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus\Handler;

use AwdStudio\Bus\Exception\InvalidHandler;
use AwdStudio\Bus\Handler\PsrContainerHandlerRegistry;
use AwdStudio\Bus\HandlerLocator;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;

/**
 * @coversDefaultClass \AwdStudio\Bus\Handler\PsrContainerHandlerRegistry
 */
final class PsrContainerHandlersTest extends BusTestCase
{
    /** @var \AwdStudio\Bus\Handler\PsrContainerHandlerRegistry */
    private $instance;

    /** @var \Prophecy\Prophecy\ObjectProphecy|\Psr\Container\ContainerInterface */
    private $containerProphecy;

    /** @var \AwdStudio\Bus\HandlerLocator|\Prophecy\Prophecy\ObjectProphecy */
    private $handlerLocatorProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->containerProphecy = $this->prophesize(ContainerInterface::class);
        $this->handlerLocatorProphecy = $this->prophesize(HandlerLocator::class);

        $this->instance = new PsrContainerHandlerRegistry(
            $this->containerProphecy->reveal(),
            $this->handlerLocatorProphecy->reveal()
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
        $this->assertNotNull(new PsrContainerHandlerRegistry($this->containerProphecy->reveal()));
    }

    /**
     * @covers ::register
     */
    public function testMustCheckAServiceWhenTriesToRegisterIt(): void
    {
        $this->containerProphecy
            ->has(Argument::exact('FooHandler'))
            ->willReturn(true)
            ->shouldBeCalledOnce();

        $this->instance->register('Foo', 'FooHandler');
    }

    /**
     * @covers ::register
     */
    public function testMustThrowAnExceptionIfAHandlerIsNotInTheServiceLocator(): void
    {
        $this->containerProphecy
            ->has(Argument::exact('FooHandler'))
            ->willReturn(false);

        $this->expectException(InvalidHandler::class);

        $this->instance->register('Foo', 'FooHandler');
    }

    /**
     * @covers ::add
     */
    public function testMustCallAnExternalHandlersToAddADynamicHandler(): void
    {
        $dynamicHandler = static function (): void { return; };

        $this->handlerLocatorProphecy
            ->add(Argument::exact(\stdClass::class), Argument::exact($dynamicHandler))
            ->shouldBeCalledOnce();

        $this->instance->add(\stdClass::class, $dynamicHandler);
    }

    /**
     * @covers ::has
     */
    public function testMustCheckAnExternalHandlersWhenLooksForAHandler(): void
    {
        $this->handlerLocatorProphecy
            ->has(Argument::exact('Foo'))
            ->willReturn(false)
            ->shouldBeCalledOnce();

        $this->instance->has('Foo');
    }

    /**
     * @covers ::has
     */
    public function testMustReturnTrueIfAHandlerIsInExternalHandlers(): void
    {
        $this->handlerLocatorProphecy
            ->has(Argument::exact('Foo'))
            ->willReturn(true);

        $this->assertTrue($this->instance->has('Foo'));
    }

    /**
     * @covers ::has
     */
    public function testMustReturnTrueIfAHandlerRegisteredInTheContainerHandlers(): void
    {
        $this->handlerLocatorProphecy
            ->has(Argument::exact('Foo'))
            ->willReturn(false);

        $this->containerProphecy
            ->has(Argument::exact('FooHandler'))
            ->willReturn(true);

        $this->instance->register('Foo', 'FooHandler');

        $this->assertTrue($this->instance->has('Foo'));
    }

    /**
     * @covers ::has
     */
    public function testMustReturnFalseIfThereAreNoHandlersNorInDynamicHandlersNotInRegisteredOnes(): void
    {
        $this->handlerLocatorProphecy
            ->has(Argument::exact('Foo'))
            ->willReturn(false);

        $this->assertFalse($this->instance->has('Foo'));
    }

    /**
     * @covers ::get
     */
    public function testMustYieldAHandlerFromDynamicHandlersIfItHasSome(): void
    {
        $dynamicHandler = static function (): void { return; };

        $this->handlerLocatorProphecy
            ->has(Argument::exact('Foo'))
            ->willReturn(true);

        $this->handlerLocatorProphecy
            ->get(Argument::exact('Foo'))
            ->willYield([$dynamicHandler]);

        $this->assertContains($dynamicHandler, $this->instance->get('Foo'));
    }

    /**
     * @covers ::get
     */
    public function testMustYieldAHandlerFromContainerIfItRegistered(): void
    {
        $dynamicHandler = static function (): void { return; };

        $this->instance->register('Foo', 'FooHandler');

        $this->handlerLocatorProphecy
            ->has(Argument::exact('Foo'))
            ->willReturn(false);

        $this->containerProphecy
            ->get(Argument::exact('FooHandler'))
            ->willReturn($dynamicHandler);

        $this->assertContains($dynamicHandler, $this->instance->get('Foo'));
    }

    /**
     * @covers ::get
     */
    public function testMustYieldAllHandlersFromBothAndDynamicHandlersAndTheContainer(): void
    {
        $dynamicHandler1 = static function (): void { return; };
        $dynamicHandler2 = static function (): void { return; };
        $dynamicHandler3 = static function (): void { return; };

        $this->instance->register('Foo', 'FooHandler1');
        $this->instance->register('Foo', 'FooHandler2');

        $this->handlerLocatorProphecy
            ->has(Argument::exact('Foo'))
            ->willReturn(true);

        $this->handlerLocatorProphecy
            ->get(Argument::exact('Foo'))
            ->willYield([$dynamicHandler3]);

        $this->containerProphecy
            ->get(Argument::any())
            ->willReturn($dynamicHandler1, $dynamicHandler2);

        $result = [];
        foreach ($this->instance->get('Foo') as $handler) {
            $result[] = $handler;
        }

        $this->assertContains($dynamicHandler1, $result);
        $this->assertContains($dynamicHandler2, $result);
        $this->assertContains($dynamicHandler3, $result);
    }
}
