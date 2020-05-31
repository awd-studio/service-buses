# Service buses in PHP

## A simple library, to implement CQRS pattern with PHP projects.

[![Build Status](https://travis-ci.org/awd-studio/service-buses.svg?branch=master)](https://travis-ci.org/awd-studio/service-buses)
[![Coverage Status](https://coveralls.io/repos/github/awd-studio/service-buses/badge.svg?branch=master)](https://coveralls.io/github/awd-studio/service-buses?branch=master)

#### Advantages:
- Provides such kind of service-buses as: `Command Bus`, `Query Bus` and `Event Bus`, for the CQRS pattern implementing.
- In a single package.
- Driven by a `Dependency Injection Container` (uses the standard - PSR-11).

#### Contents:
- [Requirements](#requirements)
- [Usage](#usage)
  - [Global configuration](#configuration)
  - [Command Bus](#command-bus)
  - [Query Bus](#query-bus)
  - [Event Bus](#event-bus)
- [Testing](#testing)

-----

## Requirements:

- PHP v7.3+
- [Composer](https://getcomposer.org/) package manager
- [PSR-11](https://github.com/php-fig/container) - compatible container (*optional*)


## Usage:

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
$handlers->add(MyCommand::class, static function (MyCommand $command): void {});

$middleware = new InMemoryHandlerLocator();
$middleware->add(MyCommand::class, static function (MyCommand $command, callable $next): void {
    // Do whatever you need before the handler
    $next($command);
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
