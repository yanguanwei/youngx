<?php

namespace Youngx\Database;

class Query
{
    /**
     * @var Schema
     */
    protected $schema;

    protected $entityType;

    protected $table;

    protected $alias;

    protected $entityClass;
    /**
     * @var Select
     */
    protected $select;
    /**
     * @var Query
     */
    protected $parent;
    /**
     * @var Query[]
     */
    protected $jointQueries = array();

    /**
     * @var Connection
     */
    protected $connection;

    protected $repository;

    protected $data;

    public function __construct(Connection $connection, Repository $repository, Schema $schema, $entityType, $alias = null, Query $parent = null)
    {
        $this->connection = $connection;
        $this->repository = $repository;
        $this->schema = $schema;

        $this->entityType = $entityType;
        $this->table = $schema->getTable($entityType);
        $this->alias = $alias ?: $this->table;
        $this->entityClass = $schema->getEntityClass($entityType);

        $this->parent = $parent;
        if ($parent === null) {
            $this->select()->from(array($this->table, $this->alias));
        }
    }

    public function __call($staticCall, array $arguments = array())
    {
        array_unshift($arguments, $this);
        return call_user_func_array(array($this->entityClass, $staticCall), $arguments);
    }

    /**
     * @param array $params
     * @return Entity[]
     */
    public function all(array $params = array())
    {
        return $this->fetch(true, $params);
    }

    /**
     * @param array $params
     * @return Entity
     */
    public function one(array $params = array())
    {
        $this->limit(1);
        return $this->fetch(false, $params);
    }

    public function total(array $params = array())
    {
        if ($this->parent) {
            return $this->parent->total($params);
        }
        return $this->connection->query($this->select()->toTotalCountSQL(), $params)->fetchColumn(0);
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return Select
     */
    public function select()
    {
        if ($this->parent) {
            return $this->parent->select();
        }

        if ($this->select) {
            return $this->select;
        }

        return $this->select = new Select($this->connection);
    }

    /**
     * @param string $target
     * @param null|string $alias
     * @param string $jointType
     * @throws \RuntimeException
     * @return Query
     */
    public function join($target, $alias = null, $jointType = 'leftJoin')
    {
        $relationship = $this->schema->getRelationship($this->entityType, $target);
        if ($relationship['relation'] === 'one_many' || $relationship['relation'] === 'many_many') {
            throw new \RuntimeException(sprintf(
                    'It is not supported in entityType[one_many] when Entity[%s] join the relationship[%s]',
                    $this->entityType, $target
                )
            );
        }

        $joinOn = array();

        if (null === $alias) {
            $alias = $target;
        }

        foreach ($relationship['condition'] as $sourceKey => $targetKey) {
            $joinOn[] = "{$alias}.{$targetKey}={$this->table}.{$sourceKey}";
        }

        $this->select()->$jointType(
            array($this->schema->getTable($relationship['entityType']), $alias),
            implode(' AND ', $joinOn)
        );

        return $this->jointQueries[$target] = new self($this->connection, $this->repository, $this->schema, $relationship['entityType'], $alias, $this);
    }

    /**
     * @param $target
     * @param null $alias
     * @return Query
     */
    public function innerJoin($target, $alias = null)
    {
        return $this->join($target, $alias, 'innerJoin');
    }

    /**
     * @param $target
     * @param null $alias
     * @return Query
     */
    public function rightJoin($target, $alias = null)
    {
        return $this->join($target, $alias, 'rightJoin');
    }

    public function leftJoinTable($table, $on)
    {
        $this->select()->leftJoin($table, $on);

        return $this;
    }

    public function rightJoinTable($table, $on)
    {
        $this->select()->rightJoin($table, $on);

        return $this;
    }

    public function innerJoinTable($table, $on)
    {
        $this->select()->innerJoin($table, $on);

        return $this;
    }

    public function where($condition, $value = null)
    {
        $this->select()->where($condition, $value);

        return $this;
    }

    public function orWhere($condition, $value = null)
    {
        $this->select()->orWhere($condition, $value);

        return $this;
    }

    public function order($order)
    {
        $this->select()->order($order);

        return $this;
    }

    public function limit($count, $offset = 0)
    {
        $this->select()->limit($count, $offset);

        return $this;
    }

    private function attachFields()
    {
        $fields = array();
        foreach ($this->schema->getFields($this->entityType) as $field) {
            $fields["{$this->alias}_" . $field] = $field;
        }
        $this->select()->addFields($this->alias, $fields);

        foreach ($this->jointQueries as $query) {
            $query->attachFields();
        }
    }

    private function resolveRawData($rawData)
    {
        $data = array();

        foreach($this->select()->getFields($this->alias) as $alias => $field) {
            $data[$field] = $rawData[$alias];
        }

        foreach ($this->jointQueries as $builder) {
            $builder->resolveRawData($rawData);
        }

        $this->data = $data;
    }

    private function fetch($multiple = false, array $params = array())
    {
        if ($this->parent) {
            return $this->parent->fetch($multiple);
        }

        $this->attachFields();

        if ($multiple) {
            $entities = array();
            foreach ($this->connection->query($this->select()->toSQL(), $params)->fetchAll() as $rawData) {
                $this->resolveRawData($rawData);
                $entities[] = $this->bind();
            }
            return $entities;
        } else {
            $rawData = $this->connection->query($this->select()->toSQL(), $params)->fetch();
            $this->resolveRawData($rawData);
            return $rawData ? $this->bind() : null;
        }
    }

    /**
     * @return Entity | null
     */
    private function bind()
    {
        $data = $this->data;
        if (isset($data[$this->schema->getPrimaryKey($this->entityType)])) {
            $entity = $this->repository->bind($this->entityType, $data);
            foreach ($this->jointQueries as $target => $query) {
                $jointEntity = $query->bind();
                if ($jointEntity) {
                    $relationship = $this->schema->hasRelationship($this->entityType, $target);
                    if ($relationship['relation'] === 'one_one' && $relationship['me']) {
                        $jointEntity->set($relationship['reverse'], $entity);
                    }
                    $entity->set($target, $jointEntity);
                }
            }
            return $entity;
        }
    }
}