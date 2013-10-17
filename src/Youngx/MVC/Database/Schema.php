<?php

namespace Youngx\MVC\Database;

use Youngx\EventHandler\Handler;
use Youngx\MVC\Application;

class Schema extends \Youngx\Database\Schema
{
    protected $handler;
    protected $app;
    protected $hasLoaded = false;

    public function __construct(Handler $handler, Application $app)
    {
        $this->handler = $handler;
        $this->app = $app;

        $handler->trigger("kernel.entity.collect", $collection = new EntityCollection($app));
        parent::__construct($collection->getEntityClasses(), $collection->getRelationships());
    }
}