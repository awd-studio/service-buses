# Service buses in PHP

## A simple library, to implement CQRS pattern with PHP projects.

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

### Command Bus:
```php
<?php

// Example Command
class MyCommand
{
    public $foo;   
    public $bar;
    
    // ... constructor and the other staff
}

// Example Command-handler
class MyCommandHandler
{
    private $dependency;
    private $anotherDependency;
    
    // Require whatever you need, it'll be managed by the DI container
    public function __construct(
        MyDependency $dependency, 
        MyAnotherDependency $anotherDependency
    ) {
        $this->dependency = $dependency;
        $this->anotherDependency = $anotherDependency;
    }
    
    // This method must be implemented by each command, 
    // otherwise, there will be thrown an exception.
    // The command to handle will be sent as an argument.
    // According to a canonical implementation of a command
    // pattern, handlers shouldn't have return statement. 
    public function handle(MyCommand $command): void
    {
        $foo = $command->foo;
        $bar = $command->bar;
        // ... process the command
    }
}

/** @var \Psr\Container\ContainerInterface $container */
$container = ... // Your DI-container which contains the handler

// Create a bus
$commandBus = new \AwdStudio\ServiceBuses\CommandBus($container, [
    MyCommand::class => MyCommandHandler::class // Assign a handler to command
]);
// Or, you can add subscribers dynamically:
$commandBus = new \AwdStudio\ServiceBuses\CommandBus($container);
$commandBus->subscribe(MyCommandHandler::class, MyCommand::class);

// Create a command
$command = new MyCommand('foo', 'bar');
$commandBus->handle($command);
```

-----

### Query Bus:
```php
<?php

// Example Query:
class MyQuery
{
    public $foo;
    public $bar;
}

// Example Query-handler
class MyQueryHandler
{
    private $entityRepository;
    private $dependency;
    private $anotherDependency;
    
    // Require whatever you need, it'll be managed by the DI container
    public function __construct(
        MyEntityRepository $entityRepository, 
        MyDependency $dependency, 
        MyAnotherDependency $anotherDependency
    ) {
        $this->entityRepository = $entityRepository;
        $this->dependency = $dependency;
        $this->anotherDependency = $anotherDependency;
    }
    
    // This method must be implemented by each command, 
    // otherwise, there will be thrown an exception.
    // The query to handle will be sent as an argument.
    // It must return the result of a query execution.
    public function dispatch(MyQuery $query): ?MyEntity
    {
        $foo = $query->foo;
        $bar = $query->bar;
        
        // ... process the query
        $result = $this->myEntityRepository->findBy($foo, $bar);
        
        return $result;
    }
}

/** @var \Psr\Container\ContainerInterface $container */
$container = ... // Your DI-container which contains the handler

// Create a bus
$queryBus = new \AwdStudio\ServiceBuses\QueryBus($container, [
    MyQuery::class => MyQueryHandler::class // Assign a handler to command
]);
// Or, you can add subscribers dynamically:
$queryBus = new \AwdStudio\ServiceBuses\QueryBus($container);
$queryBus->subscribe(MyQueryHandler::class, MyQuery::class);

// Create a query
$query = new MyQuery('foo', 'bar');
$result = $queryBus->handle($query);
```

-----

### Event Bus:
```php
<?php

// Example Event:
class MyEvent
{
    public $foo;
    public $bar;
}

// Example Event-subscriber
class MyEventSubscriber
{
    private $dependency;
    private $anotherDependency;
    
    // Require whatever you need, it'll be managed by the DI container
    public function __construct(
        MyDependency $dependency, 
        MyAnotherDependency $anotherDependency
    ) {
        $this->dependency = $dependency;
        $this->anotherDependency = $anotherDependency;
    }
    
    // This method must be implemented by each command, 
    // otherwise, there will be thrown an exception.
    // The event to handle will be sent as an argument.
    public function dispatch(MyEvent $event): void
    {
        $foo = $event->foo;
        $bar = $event->bar;
        // ... process the command
    }
}

// Example Event subscriber
class MyAnotherEventSubscriber
{ 
    public function dispatch(MyEvent $event): void
    {
        $foo = $event->foo;
        $bar = $event->bar;
        // ... process the command
    }
}

/** @var \Psr\Container\ContainerInterface $container */
$container = ... // Your DI-container which contains the handler

// Create a bus
$eventBus = new \AwdStudio\ServiceBuses\EventBus($container, [
    MyEvent::class => [                  // Assign a subscribers to an event
        MyEventSubscriber::class,        
        MyAnotherEventSubscriber::class,
    ]
]);
// Or, you can add subscribers dynamically:
$eventBus = new \AwdStudio\ServiceBuses\CommandBus($container);
$eventBus->subscribe(MyEventSubscriber::class, MyEvent::class);

// Create an Event
$event = new MyEvent('foo', 'bar');
$eventBus->dispatch($event);
```

-----

### Global configuration:

```php
<?php

/** @var \Psr\Container\ContainerInterface $container */
$container = ... // Your DI-container which contains the handler

$serviceBus = new \AwdStudio\ServiceBuses\ServiceBus($container);

// Get the Command Bus instance;
$commandBus = $serviceBus->commandBus(/* A list of command handlers */ []);

// Get the Query Bus instance;
$queryBus = $serviceBus->queryBus(/* A list of query handlers */ []);

// Get the Event Bus instance;
$eventBus = $serviceBus->eventBus(/* A list of event subscribers */ []);
```

-----

## Testing:
```bash
composer test
```
