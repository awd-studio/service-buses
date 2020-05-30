<?php

declare(strict_types=1);

namespace AwdStudio\Query;

interface IQueryBus
{
    /**
     * Handles a query.
     *
     * @param object $query
     *
     * @return mixed
     *
     * @throws \AwdStudio\Bus\Exception\NoHandlerDefined
     */
    public function handle(object $query);
}
