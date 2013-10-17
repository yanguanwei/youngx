<?php

namespace Youngx\MVC\Database;

use Youngx\MVC\Application;

class EntityCollection
{
    /**
     * @var Definition[]
     */
    protected $definitions = array();
    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param $entityClass
     * @return Definition
     */
    public function add($entityClass)
    {
        $entityClass = $this->app->resolveClass($entityClass);
        return $this->definitions[$entityClass::type()] = new Definition($entityClass);
    }

    /**
     * @param $entityType
     * @throws \InvalidArgumentException
     * @return Definition
     */
    public function get($entityType)
    {
        if (!$this->has($entityType)) {
            throw new \InvalidArgumentException(sprintf("Entity[%s]'s definition has not been defined.", $entityType));
        }
        return $this->definitions[$entityType];
    }

    public function has($entityType)
    {
        return isset($this->definitions[$entityType]);
    }

    public function getEntityClasses()
    {
        $entityClasses = array();
        foreach ($this->definitions as $entityType => $definition) {
            $entityClasses[$entityType] = $definition->getEntityClass();
        }
        return $entityClasses;
    }

    public function getRelationships()
    {
        $relationships = array();
        foreach ($this->definitions as $entityType => $definition) {
            if ($relationship = $definition->getRelationship()) {
                $relationships[$entityType] = $relationship;
            }
        }
        return $relationships;
    }
}

class Definition
{
    protected $entityClass;
    protected $relationship = array();

    public function __construct($entityClass)
    {
        $this->setEntityClass($entityClass);
    }

    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    public function relate($field, $entityType, $condition, $relation, $reverse = null)
    {
        $this->relationship[$field] = array(
            'entityType' => $entityType,
            'condition' => $condition,
            'relation' => $relation,
            'reverse' => $reverse
        );

        return $this;
    }

    public function getEntityClass()
    {
        return $this->entityClass;
    }

    public function getRelationship()
    {
        return $this->relationship;
    }
}