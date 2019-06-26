<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\QueryBus;

interface QueryBusInterface
{

    /**
     * Added a query-handler to the list of handlers by the query which it handles.
     *
     * @param string $queryHandler The handler's service ID.
     * @param string $query        The query class to handle.
     *
     * @return \AwdStudio\ServiceBuses\QueryBus\QueryBusInterface
     */
    public function subscribe(string $queryHandler, string $query): self;

    /**
     * Handles a query with a handler from the collection.
     *
     * @param object $query
     *
     * @return mixed
     * @throws \AwdStudio\ServiceBuses\QueryBus\Exception\QueryHandlerNotDefined
     * @throws \AwdStudio\ServiceBuses\QueryBus\Exception\QueryHandlerIsNotAppropriate
     */
    public function handle($query);

}
