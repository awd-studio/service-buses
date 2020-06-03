# Service buses in PHP

## A simple library, to implement `CQRS`-ish pattern on PHP projects.

[![Build Status](https://travis-ci.org/awd-studio/service-buses.svg?branch=master)](https://travis-ci.org/awd-studio/service-buses)
[![Coverage Status](https://coveralls.io/repos/github/awd-studio/service-buses/badge.svg?branch=master)](https://coveralls.io/github/awd-studio/service-buses?branch=master)

#### Features:
- Nither messages nor handlers don't need to extend or implement any additional abstraction.
- Supports `middleware` for handlers.
- A handler (as well as middleware) can be any of `callable` item.
- Handlers can subscribe on any of parents or implementations of an event.
- Contains a decorator to register handles as services handled via `PSR-11`'s container.
- Contains a decorator to autosubscribe handlers by a typehint on a message that it handles.
- Provides ready to go bus patterns such a `Command Bus`, a `Query Bus` and an `Event Bus`.
- Supports passing additional parameters to the buses to send to handlers.

#### Contents:
- [Get started](#get-started)
- [Handling messages](#handling-messages)
- [Predefined buses](#predefined-buses)
  - [Command Bus](#command-bus)
  - [Query Bus](#query-bus)
  - [Event Bus](#event-bus)
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

## Predefined buses:

### Command bus:
```php
<?php

use AwdStudio\Bus\Handler\InMemoryHandlerLocator;
use AwdStudio\Bus\Middleware\MiddlewareChain;
use AwdStudio\Command\CommandBus;

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
$chain = new MiddlewareChain($middleware);

$bus = new CommandBus($handlers, $chain);

$bus->handle(new MyCommand());
```

-----

## Testing:
```bash
composer test
```
