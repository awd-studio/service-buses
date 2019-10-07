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
  - [Command Bus](#command-bus)
  - [Query Bus](#query-bus)
  - [Event Bus](#event-bus)
- [Testing](#testing)
- [Global configuration](#global-configuration)

-----

## Requirements:

- PHP v7.1+
- [Composer](https://getcomposer.org/) package manager
- [PSR-11](https://github.com/php-fig/container)\-compatible container


## Usage:

### Configuration:
```php
<?php

use AwdStudio\ServiceBuses\Implementation\Handling\ContainerHandlerLocator;
use AwdStudio\ServiceBuses\Implementation\Handling\InMemoryHandlerLocator;
use AwdStudio\ServiceBuses\Implementation\Middleware\ChannelChain;
use AwdStudio\ServiceBuses\Implementation\Middleware\Chain;

// Middleware also must be invokable
class MyMiddleware
{
    // You can use types to define processing command
    public function __invoke(MyCommand $command): void
    {
        // Process the command ...
    }    
}


// You can use predefined handler locators
$handlers = new InMemoryHandlerLocator([
    MyCommand::class => new MyCommandHandler() // Assign a handler to the command it manages
]);

// There also is a handler locator to work with a service-container
$handlers = new ContainerHandlerLocator($myPsr11Container);
$handlers->add(MyCommand::class, MyCommandHandler::class);


// To fill middleware there are also a couple of predefined options

// A simple middleware chain:
$middlewareChain = new Chain();
$middlewareChain->add(new MyMiddleware());

// A chain which processes middleware by channels (by processed command):
$middlewareChain = new ChannelChain();
$middlewareChain->add(new MyMiddleware()); // Will be called only for the MyCommand
```

### Command Bus:
```php
<?php

use AwdStudio\ServiceBuses\CommandBus\CommandBus;

// Create any command you need
class MyCommand 
{
    public $value;
}


// Handlers supposed to be invokable
class MyCommandHandler 
{
    // You can use types to define processing command
    public function __invoke(MyCommand $command): void
    {
        // Process the command ...
    }    
}

$commandBus = new CommandBus($handlers, $middlewareChain);
$commandBus->handle(new MyCommand());
```

-----

### Query Bus:
```php
<?php

use AwdStudio\ServiceBuses\QueryBus\QueryBus;

// Create any query you need
class MyQuery
{
    public $value;
}


// Handlers supposed to be invokable
class MyQueryHandler 
{
    // You can use types to define processing query
    public function __invoke(MyQuery $query): array
    {
        // Process the query and return the result ...
    }    
}

$queryBus = new QueryBus($handlers, $middlewareChain);
$result = $queryBus->handle(new MyQuery());
```

-----

### Event Bus:
```php
<?php

use AwdStudio\ServiceBuses\EventBus\EventBus;

// Create any event you need
class MyEvent 
{
    public $value;
}


// Handlers supposed to be invokable
class MyEventHandler 
{
    // You can use types to define processing event
    public function __invoke(MyEvent $event): void
    {
        // Process the event ...
    }    
}

$eventBus = new EventBus($handlers, $middlewareChain);
$eventBus->handle(new MyEvent());
```

-----

## Testing:
```bash
composer test
```
