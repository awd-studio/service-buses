# Service buses in PHP

## A simple library, to implement `CQRS`-ish pattern on PHP projects.

![Build status](https://github.com/awd-studio/service-buses/actions/workflows/tests.yml/badge.svg?branch=feature-1)
[![Coverage Status](https://coveralls.io/repos/github/awd-studio/service-buses/badge.svg?branch=master)](https://coveralls.io/github/awd-studio/service-buses?branch=master)

#### Features:
- Neither messages nor handlers don't need to extend or implement any additional abstraction.
- Supports `middleware` for handlers.
- A handler (as well as middleware) can be any of `callable` item.
- Handlers can subscribe on any of parents or implementations of an event.
- Contains a decorator to register handles as services handled via `PSR-11`'s container.
- Contains a decorator to auto-subscribe handlers by a typehint on a message that it handles.
- Provides ready to go bus patterns such a `Command Bus`, a `Query Bus` and an `Event Bus`.
- Supports passing additional parameters to the buses to send to handlers.

#### Contents:
- [Get started](#get-started)
- [Handling messages](#handling-messages)
- [Middleware](#middleware)
- [Predefined buses](#predefined-buses)
  - [Command Bus](#command-bus)
  - [Query Bus](#query-bus)
  - [Event Bus](#event-bus)
- [Subscribe on parents](#subscribe-on-parents)
- [Services as handlers](#services-as-handlers)
  - [Auto-register services](#auto-register-services)
  - [Using your own handling methods](#using-your-own-handling-methods)
- [Passing additional arguments](#passing-additional-arguments)
- [Define custom bus](#define-custom-bus)
- [Testing](#testing)

-----

## Get started:

### Requirenments:
- PHP 7.3+
- [PSR-11](https://github.com/php-fig/container) - compatible container (*optional*)

### Install:
```sh
composer require awd-studio/service-buses
```


## Handling messages:

A message, is nothing, but a simple PHP-object.

It can contain any data you need, but usually, it's better to provide some immutable messages, that can be serialized.
```php
<?php

class MyMessage {}
```

Anyway, you are able to extend or implement anything you need.
```php
<?php

interface MessageInterface {}

abstract class ParentMessage {}

final class MyMessage extends ParentMessage implements MessageInterface {}
```

A handler-locator is a repository for handlers.

With them, we can assign a handler to particular messages.
Library provides some handler locators, for example - a locator to store handlers in memory:
```php
<?php

use AwdStudio\Bus\Handler\InMemoryHandlerLocator;

$handlers = new InMemoryHandlerLocator();

// To assign a handler we can call a method `add`.
// As a "messageId" we send the FCQN of a message that we assign on.
// A handler must be any callable PHP-item. 
$handlers->add(\stdClass::class, static function (\stdClass $message): void {});

// Now, we've got a handler that handles a message of type "stdClass".
// But, we can add more than one handler per message. 
// Actually, it's not limited, but keep in mind the patterns
// such Command-bus or Query-bus that suppose to use the only one handler
// per a message that they handle.
// So, we can add more handlers to same message, for example a callable object:
$handler = new class {
    public function __invoke(\stdClass $message): void {}
};
$handlers->add(\stdClass::class, $handler);

// So now, we have 2 handlers that are going to be released 
// when somebody tries get them:
$handlers->get(\stdClass::class);

// To check if there are some handlers for certain message 
// there is a method `has`:
$handlers->has(\stdClass::class); // true|false
```


To handle a message, the bus needs to be called. 
For instance, we have a bus that extends provided SimpleBus. 

We're gonna use a 
```php
<?php

use AwdStudio\Bus\Handler\InMemoryHandlerLocator;

// We need to use a handler locator, from which a bus will get handlers
$bus = new class(new InMemoryHandlerLocator()) extends \AwdStudio\Bus\SimpleBus {
    // We need to provide a method that will handle our message
    public function handle(object $message): void 
    {
        // Our parent allows us to iterate all handlers 
        // that assigned to certain message
        foreach ($this->handleAll($message) as $result) {
            echo $result;
        }
    }
};

// To use a bus, we call a provided method:
$bus->handle(new \stdClass());
```


## Middleware

Middleware layer is simple list of callbacks, that arranges in a chain and are executed within single handler.

They are very close to handles. 
A middleware is also a callback, the only difference is that middleware has a bit different signature.

Except the message and additional parameters it accepts another callback, usually it calls `$next`. 
The rest of signature usually repeats handler's one. 

Each middleware should call this `$next` callback. 
But, it can rule when to call it, and it is possible to run main logic before or after the main handler.

For example:
```php
<?php

class StdClassHandler {
    public function __invoke(\stdClass $message): void
    {
        echo 42;
    }
}

class StdClassBeforeMiddleware {
    public function __invoke(\stdClass $message, callable $next): void
    {
        echo 24;

        $next();
    }
}

class StdClassAfterMiddleware {
    public function __invoke(\stdClass $message, callable $next): void
    {
        $next();

        echo 69;
    }
}

// When a middleware-bus handles a stdClass-message, the order will be such:
// -> 24
// -> 42
// -> 69
```

If the handler must return some value, middleware have to as well:
```php
<?php

class StdClassHandler {
    public function __invoke(\stdClass $message): int
    {
        return 42;
    }
}

class StdClassBeforeMiddleware {
    public function __invoke(\stdClass $message, callable $next): int
    {
        $result = $next();

        return $result + 69;
    }
}

// The whole result will be 111;
```


## Predefined buses:

There are a few predefined buses: 
- `\AwdStudio\Command\CommandBus` *(The Command-bus pattern akka `C` in `CQRS`)*
  - `\AwdStudio\Command\MiddlewareCommandBus` - Handles a command, within middleware, via single handler.
  

- `\AwdStudio\Query\QueryBus` *(The Query-bus pattern akka `Q` in `CQRS`)*
  - `\AwdStudio\Query\MiddlewareQueryBus` - Handles a query, within middleware, via single handler.


- `\AwdStudio\Event\EventBus` *(Observer-subscriber pattern)*
  - `\AwdStudio\Event\MiddlewareEventBus` - Dispatches an event, to each subscriber (can be `>= 0`), within middleware.

### Command-bus:

```php
<?php

use AwdStudio\Bus\Handler\InMemoryHandlerLocator;
use AwdStudio\Bus\Middleware\CallbackMiddlewareChain;
use AwdStudio\Command\SimpleCommandBus;

class MyCommand {
    // Messages might be any of PHP class.
    // No any of implementation or extending required.
}

$handlers = new InMemoryHandlerLocator();
// Register a handler. It can be any callable thing.
$handlers->add(MyCommand::class, static function (MyCommand $command): void {});

$middleware = new InMemoryHandlerLocator();
// Register a middleware. It can be any callable thing as well. 
// The only thing is that it gets a callback with next middleware as a 2nd param.
$middleware->add(MyCommand::class, static function (MyCommand $command, callable $next): void {
    // Do whatever you need before the handler.
    $next(); // Just dont forget to call a next callback.
    // Or after...
});
$chain = new CallbackMiddlewareChain($middleware);

$bus = new SimpleCommandBus($handlers, $chain);

$bus->handle(new MyCommand());
```


### Query-bus:

```php
<?php

use AwdStudio\Bus\Handler\InMemoryHandlerLocator;
use AwdStudio\Bus\Middleware\CallbackMiddlewareChain;
use AwdStudio\Query\SimpleQueryBus;

class MyQuery {
    // Messages might be any of PHP class.
    // No any of implementation or extending required.
}

$handlers = new InMemoryHandlerLocator();
// Register a handler. It can be any callable thing.
$handlers->add(MyQuery::class, static function (MyQuery $query): string {
    return 'foo';
});

$middleware = new InMemoryHandlerLocator();
// Register a middleware. It can be any callable thing as well. 
// The only thing is that it gets a callback with next middleware as a 2nd param.
$middleware->add(MyQuery::class, static function (MyQuery $query, callable $next): string {
    // Do whatever you need before the handler.
    $result = $next(); // Just dont forget to call a next callback.

    return 'prefix ' . $result . ' suffix';
    // Or after...
});
$chain = new CallbackMiddlewareChain($middleware);

$bus = new SimpleQueryBus($handlers, $chain);

$result = $bus->handle(new MyQuery());

// Result will be:
// -> prefix foo suffix
```


### Event-bus:

```php
<?php

use AwdStudio\Bus\Handler\InMemoryHandlerLocator;
use AwdStudio\Bus\Middleware\CallbackMiddlewareChain;
use AwdStudio\Event\SimpleEventBus;

class MyEvent {
    // Messages might be any of PHP class.
    // No any of implementation or extending required.
}

$subscribers = new InMemoryHandlerLocator();
// Register a handler. It can be any callable thing.
$subscribers->add(MyEvent::class, static function (MyEvent $event): void {});
// As the event-bus pattern allows to provide any amount of subscribers
// we cah add more of them:
$subscribers->add(MyEvent::class, static function (MyEvent $event): void {});

$middleware = new InMemoryHandlerLocator();
// Register a middleware. It can be any callable thing as well. 
// The only thing is that it gets a callback with next middleware as a 2nd param.
$middleware->add(MyEvent::class, static function (MyEvent $event, callable $next): void {
    // Do whatever you need before the handler.
    $next(); // Just dont forget to call a next callback.
    // Or after...
});
$chain = new CallbackMiddlewareChain($middleware);

$bus = new SimpleEventBus($subscribers, $chain);

$bus->handle(new MyEvent());

// After that, the event is delivered to each subscriber.
// And each of subscriber is wrapped with all middleware.  
```


## Subscribe on parents

The library allows subscribing not only on a certain class, but on all of its parents - either a parent or an implementation from any level.

```php
<?php

use AwdStudio\Bus\Handler\ParentsAwareClassHandlerRegistry;
use AwdStudio\Bus\Handler\PsrContainerClassHandlerRegistry;
use Psr\Container\ContainerInterface;

class MyPsr11Container implements ContainerInterface {}

interface Foo {}
abstract class Bar {}
final class Baz extends Bar implements Foo {}

class Handler
{
    // You can subscribe on any of level
    public function __invoke(Foo $message): void {}
    // ..or
    public function __invoke(Bar $message): void {}
    // ..or
    public function __invoke(Baz $message): void {}
}

$handlerRegistry = new ParentsAwareClassHandlerRegistry(new PsrContainerClassHandlerRegistry(new MyPsr11Container()));
```


## Services as handlers

Of course, to resolve the only callbacks as handlers is not such a convenient way to build projects. 
Fortunately, we have standards as a `PSR-11` for such common use-cases as implementation of `DIP`. 
And, the library provides ability to use those containers as service locators for resolving handlers as DI.

To use it, there is a decorator for a handler-locator, that can be used for registering handlers with just FCQN. 
As a dependency it accepts any of `Psr\Container\ContainerInterface`, that supposed to resolve handlers.

```php
<?php

use AwdStudio\Bus\Handler\PsrContainerClassHandlerRegistry;
use AwdStudio\Bus\SimpleBus;
use Psr\Container\ContainerInterface;

class MyPsr11Container implements ContainerInterface
{
    private $dependencies;

    public function __construct(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }

    public function has($id): bool
    {
        return \in_array($id, $this->dependencies, true);
    }

    public function get($id): object
    {
        return $id();
    }
}

class StdClassHandler
{
    public function __invoke(\stdClass $message): void
    {
        $message->foo = 'foo';
    }
}

$serviceLocator = new MyPsr11Container([StdClassHandler::class]);
$handlerRegistry = new PsrContainerClassHandlerRegistry($serviceLocator);

// To assign a handler use a defined method:
$handlerRegistry->register(\stdClass::class, StdClassHandler::class);

// And pass them as a handler-locator to a bus
$bus = new class ($handlerRegistry) extends SimpleBus {
    public  function handle(object $message): void 
    {
        foreach ($this->handleAll($message) as $result) {
            echo $result;
        }
    }
};

// After that, you can call handling as usual:
$bus->handle(new \stdClass()); // The handler will be executed
```


### Auto-register services

There is even a decorator to subscribe callbacks automatically, by their signature, that supposed to contain a type-hint as the very first parameter.

```php
<?php

use AwdStudio\Bus\Handler\AutoRegisterHandlersRegistryClass;
use AwdStudio\Bus\Handler\PsrContainerClassHandlerRegistry;

$psrRegistry = new PsrContainerClassHandlerRegistry(new  MyPsr11Container());
$autoRegistry = new AutoRegisterHandlersRegistryClass($psrRegistry);

// Now, you can add a callback to assign a handler automatically.
// Just be sure, that it has a correct type-hint of a message that it handles.
$handler = static function (\stdClass $message): void { };
$autoRegistry->autoAdd($handler); // It will be called within the stdClass' messages.

// And this is not all it can! 
// If you use services as handlers - you also can register them automatically. 
// Suppose we have this handler, that can be resolved from our container:
class Handler {
    public function __invoke(\stdClass $message): void { }
}

// We can register it like so:
$autoRegistry->autoRegister(Handler::class);

// That's all..
```


### Using your own handling methods

If you don't like invokable services, or somehow need to use handlers that handle via different methods - this is not a problem at all. 

Just pass the name of a method while registering:

```php
<?php

use AwdStudio\Bus\Handler\PsrContainerClassHandlerRegistry;

class Handler {
    public function handle(\stdClass $message): void { }
}

// Any registry can manage with it out of the box
$psrRegistry = new PsrContainerClassHandlerRegistry(new  MyPsr11Container());
$psrRegistry->register(\stdClass::class, Handler::class, 'handle');
// The 3rd argument tells which method is in charge of handling.
```


## Passing additional arguments

Despite the fact, bus' patterns assume to use the only a message to handle, this library allows passing more than one argument to the bus. 
It can be useful in cases when you need to rely on some additional context. 

All additional arguments that are passed to the bus will be sent as parameters to each handler and middleware.
```php
<?php

$handler1 = static function(\stdClass $message, int $foo): void {};
$handler2 = static function(\stdClass $message, int $foo, string $bar): void {};
$handler3 = static function(\stdClass $message, int $foo, string $bar, array $baz): void {};

$middleware1 = static function(\stdClass $message, callable $next): void {};
$middleware2 = static function(\stdClass $message, callable $next, int $foo): void {};
$middleware3 = static function(\stdClass $message, callable $next, int $foo, string $bar, array $baz): void {};

// It'd work for all handlers
$bus->handle(new \stdClass(), 42, 'quu', ['any', 'data', 'you', 'need']);
```


## Define custom bus

To define your own bus, you can extend one of predefined ones. 
You have 2 options:
```php
<?php

use AwdStudio\Bus\SimpleBus;

class MyBus extends SimpleBus
{
    public function handle(object $message): string 
    {
        $result = '';
        foreach ($this->handleAll($message) as $handled) {
            $result .= $handled;
        }
    
        return $result;
    }
}
```
The SimpleBus provides you an ability to handle messages with only handles.

```php
<?php

use AwdStudio\Bus\SimpleBus;

class MyBus extends SimpleBus
{
    public function handle(object $message): string 
    {
        $result = '';
        foreach ($this->handleMessage($message) as $chain) {
            $result .= $chain();
        }
    
        return $result;
    }
}
```
The MiddlewareBus does the same, but it allows wrapping handlers with middleware.


-----


## Testing:
```bash
composer test
```
