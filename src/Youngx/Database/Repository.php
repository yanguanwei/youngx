<?php

namespace Youngx\Database;

use Doctrine\Common\Cache\Cache;
use Youngx\EventHandler\Handler;

class Repository
{
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var Schema
     */
    protected $schema;
    /**
     * @var Handler
     */
    protected $handler;

    protected $cache;

    protected $entities = array();

    public function __construct(Connection $connection, Schema $schema, Handler $handler, Cache $cache)
    {
        $this->connection = $connection;
        $this->schema = $schema;
        $this->handler = $handler;
        $this->cache = $cache;
    }

    public function delete(Entity $entity)
    {
        $type = $entity->type();
        $table = $entity->table();

        $entity->beforeDelete();
        $this->handler->trigger(array("kernel.entity.delete.before#{$type}", "kernel.entity.delete.before"), $entity);

        $this->connection->delete(
            "{$table}",
            "{$entity->primaryKey()}=:__primaryKey__",
            array(':__primaryKey__' => $entity->identifier())
        );

        $entity->afterDelete();
        $this->handler->trigger(array("kernel.entity.delete#{$type}", "kernel.entity.delete"), $entity);

        $this->deleteCachedEntity($entity);
    }

    /**
     * @param $entityType
     * @param array $data
     * @return Entity
     */
    public function bind($entityType, array $data)
    {
        $class = '\\' . $this->schema->getEntityClass($entityType);
        $entity = new $class($this);

        $entity->bind($data);

        return $entity;
    }

    /**
     * @param $entityType
     * @param array $data
     * @return Entity
     */
    public function create($entityType, array $data = array())
    {
        $class = '\\' . $this->schema->getEntityClass($entityType);
        $entity = new $class($this);

        if ($data) {
            $entity->set($data);
        }

        return $entity;
    }

    public function count($entityType, $condition = null, array $params = array())
    {
        $query = $this->query($entityType);

        if ($condition) {
            $query->where($condition);
        }

        return intval($query->total($params));
    }

    /**
     * @param $entityType
     * @param null $condition
     * @param array $params
     * @param Entity | null $entity
     * @return bool
     */
    public function exist($entityType, $condition = null, array $params  = array(), $entity = null)
    {
        $query = $this->query($entityType);

        if ($entity) {
            $query->where($entity->primaryKey() . '<>?', $entity->identifier());
        }

        if ($condition) {
            $query->where($condition);
        }

        return intval($query->total($params)) > 0;
    }

    /**
     * @param $entityType
     * @param $condition
     * @param array $params
     * @return Entity
     */
    public function find($entityType, $condition, array $params = array())
    {
        $entity = $this->query($entityType)->where($condition)->one($params);
        if ($entity) {
            $this->entities[$entity->identifier()] = $entity;
        }
        return $entity;
    }

    /**
     * @param string $entityType
     * @param string | array | null $condition
     * @param array $params
     * @return Entity[]
     */
    public function findAll($entityType, $condition = null, array $params = array())
    {
        $query = $this->query($entityType);
        if ($condition) {
            $query->where($condition);
        }
        return $query->all($params);
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param $entityType
     * @param $id
     * @return Entity
     */
    public function load($entityType, $id)
    {
        $entity = $this->loadCachedEntity($entityType, $id);

        if ($entity) {
            return $entity;
        }

        $entity = $this->find($entityType, $this->schema->getPrimaryKey($entityType).'=:__id__', array(':__id__' => $id));

        if ($entity) {
            $this->cachingEntity($entity);
        }

        return $entity;
    }

    /**
     * @param $entityType
     * @param array $ids
     * @return Entity[]
     */
    public function loadMultiple($entityType, array $ids)
    {
        $unloadedIds = $entities = array();
        foreach ($ids as $id) {
            if (null ==  $this->loadCachedEntity($entityType, $id)) {
                $unloadedIds[] = $id;
            }
        }

        if ($unloadedIds) {
            $newEntities = $this->findAll($entityType, array(
                    $this->schema->getPrimaryKey($entityType) . ' IN (?)' => $unloadedIds
                ));
            foreach ($newEntities as $entity) {
                $this->cachingEntity($entity);
            }
        }

        foreach ($ids as $id) {
            if (null !== ($entity = $this->loadCachedEntity($entityType, $id))) {
                $entities[] = $entity;
            }
        }
        return $entities;
    }

    protected function loadCachedEntity($entityType, $id)
    {
        if (isset($this->entities[$entityType][$id])) {
            return $this->entities[$entityType][$id];
        }

        $key = "entity.{$entityType}.$id";
        if (false !== $entity = $this->cache->fetch($key)) {
            $entity->repository($this);
            return $this->entities[$entityType][$id] = $entity;
        }

        return null;
    }

    public function cachingEntity(Entity $entity)
    {
        $this->cache->save("entity.{$entity->type()}.{$entity->identifier()}", $entity, 86400);
    }

    public function deleteCachedEntity(Entity $entity)
    {
        unset($this->entities[$entity->type()][$entity->identifier()]);
        $this->cache->delete("entity.{$entity->type()}.{$entity->identifier()}");
    }

    /**
     * @param $entityType
     * @param string | null $alias
     * @return Query
     */
    public function query($entityType, $alias = null)
    {
        return new Query($this->connection, $this, $this->schema, $entityType, $alias);
    }

    public function resolveExtraFieldValue(Entity $entity, $field)
    {
        $entityType = $entity->type();
        if ($this->schema->hasRelationship($entityType, $field)) {
            $relationship = $this->schema->getRelationship($entityType, $field);

            $condition = array();
            foreach ($relationship['condition'] as $sourceKey => $targetKey) {
                if (is_int($sourceKey)) {
                    $condition[] = $targetKey;
                } else {
                    $condition["{$targetKey}=?"] = $entity->get($sourceKey);
                }
            }

            if ($relationship['relation'] === 'one_many' || $relationship['relation'] == 'many_many') {
                $targetEntities = $this->query($relationship['entityType'])->where($condition)->all();
                if ($relationship['reverse']) {
                    foreach ($targetEntities as $targetEntity) {
                        $targetEntity->set($relationship['reverse'], $entity);
                    }
                }
                return $targetEntities;
            } else {
                $targetEntity = $this->query($relationship['entityType'])->where($condition)->one();
                if ($targetEntity && $relationship['relation'] === 'one_one' && $relationship['reverse']) {
                    $targetEntity->set($relationship['reverse'], $entity);
                }
                return $targetEntity;
            }
        } else {
            return $this->handler->triggerForValue(array(
                    "kernel.entity#{$entityType}.field.{$field}",
                    "kernel.entity.field.{$field}",
                ), $entity, $field);
        }
    }

    public function save(Entity $entity)
    {
        $entityType = $entity->type();
        $table = $entity->table();
        $primaryKey = $entity->primaryKey();

        $this->handler->trigger(array("kernel.entity.beforeSave#{$entityType}", "kernel.entity.beforeSave"), $entity);

        $data = $entity->toData();

        if ($entity->isNew()) {
            $entity->beforeInsert();
            $this->handler->trigger(array("kernel.entity.beforeInsert#{$entityType}", "kernel.entity.beforeInsert"), $entity);
            $id = $this->connection->insert("{$table}", $data);
            $entity->afterInsert($id);
            $this->handler->trigger(array("kernel.entity.insert#{$entityType}", "kernel.entity.insert"), $entity);
        } else {
            $entity->beforeUpdate();
            $this->handler->trigger(array("kernel.entity.beforeUpdate#{$entityType}", "kernel.entity.beforeUpdate"), $entity);
            unset($data[$primaryKey]);
            $this->connection->update(
                "{$table}",
                $data,
                "{$primaryKey}=:__primaryKey__",
                array(':__primaryKey__' => $entity->identifier())
            );
            $entity->afterUpdate();
            $this->handler->trigger(array("kernel.entity.update#{$entityType}", "kernel.entity.update"), $entity);

            $this->deleteCachedEntity($entity);
        }

        $this->handler->trigger(array("kernel.entity.save#{$entityType}", "kernel.entity.save"), $entity);
    }
}