<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Unit\Bus\Handler;

use AwdStudio\Bus\Exception\InvalidHandler;
use AwdStudio\Bus\Handler\AutoRegisterHandlersRegistry;
use AwdStudio\Bus\Handler\HandlerRegistry;
use AwdStudio\Bus\Reader\MessageIdResolver;
use AwdStudio\Tests\BusTestCase;
use Prophecy\Argument;

class FooCallback
{
    public function __invoke(): void
    {
    }
}

/**
 * @coversDefaultClass \AwdStudio\Bus\Handler\AutoRegisterHandlersRegistry
 */
class AutoRegisterHandlersRegistryTest extends BusTestCase
{
    /** @var \AwdStudio\Bus\Handler\AutoRegisterHandlersRegistry */
    private $instance;
    /** @var \AwdStudio\Bus\Handler\HandlerRegistry|\Prophecy\Prophecy\ObjectProphecy */
    private $handelerLocatorProphecy;
    /** @var \AwdStudio\Bus\Reader\MessageIdResolver|\Prophecy\Prophecy\ObjectProphecy */
    private $readerProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handelerLocatorProphecy = $this->prophesize(HandlerRegistry::class);
        $this->readerProphecy = $this->prophesize(MessageIdResolver::class);

        $this->instance = new AutoRegisterHandlersRegistry(
            $this->handelerLocatorProphecy->reveal(),
            $this->readerProphecy->reveal()
        );
    }

    /**
     * @covers ::__construct
     */
    public function testMustImplementAHandlerRegistry(): void
    {
        $this->assertInstanceOf(HandlerRegistry::class, $this->instance);
    }

    /**
     * @covers ::__construct
     */
    public function testMustBeAbleToConstructWithoutAReader(): void
    {
        $this->assertNotNull(new  AutoRegisterHandlersRegistry($this->handelerLocatorProphecy->reveal()));
    }

    /**
     * @covers ::has
     */
    public function testMustDelegateHavingToTheParent(): void
    {
        $this->handelerLocatorProphecy
            ->has(Argument::exact('Foo'))
            ->willReturn(true)
            ->shouldBeCalledOnce();

        $this->assertTrue($this->instance->has('Foo'));
    }

    /**
     * @covers ::add
     */
    public function testMustDelegateToAddingHasToTheParent(): void
    {
        $handler = static function (): void { };
        $this->handelerLocatorProphecy
            ->add(Argument::exact('Foo'), Argument::exact($handler))
            ->shouldBeCalledOnce();

        $this->instance->add('Foo', $handler);
    }

    /**
     * @covers ::register
     */
    public function testMustDelegateRegisteringToTheParent(): void
    {
        $this->handelerLocatorProphecy
            ->register(Argument::exact('Foo'), Argument::exact('FooHandler'))
            ->shouldBeCalledOnce();

        $this->instance->register('Foo', 'FooHandler');
    }

    /**
     * @covers ::get
     */
    public function testMustDelegateGettingToTheParent(): void
    {
        $handler = static function (): void { };
        $this->handelerLocatorProphecy
            ->get(Argument::exact(\stdClass::class))
            ->willYield([$handler])
            ->shouldBeCalledOnce();

        $iterator = $this->instance->get(\stdClass::class);
        $this->assertSame($handler, $iterator->current());
    }

    /**
     * @covers ::autoAdd
     */
    public function testMustResolveTheMessageIdFromCallbackWithinTheReader(): void
    {
        $callback = static function (): void { };
        $this->readerProphecy
            ->read(Argument::type(\ReflectionFunction::class))
            ->willReturn(\stdClass::class)
            ->shouldBeCalledOnce();

        $this->handelerLocatorProphecy
            ->add(Argument::exact(\stdClass::class), Argument::exact($callback))
            ->shouldBeCalledOnce();

        $this->instance->autoAdd($callback);
    }

    /**
     * @covers ::autoRegister
     */
    public function testMustResolveTheMessageIdFromServiceWithinTheReader(): void
    {
        $this->handelerLocatorProphecy
            ->has(Argument::exact(FooCallback::class))
            ->willReturn(true);

        $this->readerProphecy
            ->read(Argument::type(\ReflectionMethod::class))
            ->willReturn(\stdClass::class)
            ->shouldBeCalledOnce();

        $this->handelerLocatorProphecy
            ->register(
                Argument::exact(\stdClass::class),
                Argument::exact(FooCallback::class),
                Argument::exact('__invoke')
            )
            ->shouldBeCalledOnce();

        $this->instance->autoRegister(FooCallback::class);
    }

    /**
     * @covers ::autoRegister
     */
    public function testMustThrowAnExceptionIfTheTheHandlerDoesNotHaveTheMethod(): void
    {
        $this->handelerLocatorProphecy
            ->has(Argument::any())
            ->willReturn(true);

        $this->expectException(InvalidHandler::class);

        $this->instance->autoRegister(FooCallback::class, 'bar');
    }
}
