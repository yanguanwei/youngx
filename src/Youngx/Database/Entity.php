<?php

namespace Youngx\Database;

abstract class Entity implements EntityInterface
{
    private $_isNew = true;
    private $_repository;
    private $_extraFieldValues = array();
    private $_unchangedEntity;

    public function __construct(Repository $repository)
    {
        $this->_repository = $repository;
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        $this->_extraFieldValues[$key] = $value;
    }

    public function __sleep()
    {
        return $this->fields();
    }

    public function __wakeup()
    {
        $this->_isNew = false;
        $this->_unchangedEntity = clone $this;
    }

    public function __set_state()
    {
        return $this->toArray();
    }

    public function bind(array $data)
    {
        if (!$data[$this->primaryKey()]) {
            throw new \RuntimeException(sprintf(
                'You should specify the value of primary kye on Entity[%s] bind.', $this->type()
            ));
        }

        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        $this->_isNew = false;
        $this->_unchangedEntity = clone $this;
    }

    public function delete()
    {
        $this->repository()->delete($this);
    }

    public function get($key)
    {
        if (method_exists($this, $method = 'get' . (strpos($key, '_') === false ? ucfirst($key) : implode(array_map('ucfirst', explode('_', $key)))))) {
            return $this->$method();
        } else {
            return $this->resolveExtraFieldValue($key);
        }
    }

    protected function resolveExtraFieldValue($key)
    {
        if (!isset($this->_extraFieldValues[$key])) {
            $this->_extraFieldValues[$key] = $this->_repository->resolveExtraFieldValue($this, $key);
        }
        return $this->_extraFieldValues[$key];
    }

    public function identifier()
    {
        $primaryKey = $this->primaryKey();
        return $this->$primaryKey;
    }

    public function isNew()
    {
        return $this->_isNew;
    }

    /**
     * @param Repository $repository
     * @return Repository
     */
    public function repository(Repository $repository = null)
    {
        if (null !== $repository) {
            return $this->_repository = $repository;
        }
        return $this->_repository;
    }

    public function save()
    {
        $this->repository()->save($this);
        $this->_unchangedEntity = clone $this;
    }

    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            if (method_exists($this, $method = 'set' . (strpos($key, '_') === false ? ucfirst($key) : implode(array_map('ucfirst', explode('_', $key)))))) {
                $this->$method($value);
            }
        }

        return $this;
    }

    public function toArray()
    {
        $array = array();
        foreach ($this->fields() as $key) {
            if (method_exists($this, $method = 'get' . (strpos($key, '_') === false ? ucfirst($key) : implode(array_map('ucfirst', explode('_', $key)))))) {
                $array[$key] = $this->$method();
            }
        }
        return $array;
    }

    public function toData()
    {
        $data = array();
        foreach ($this->fields() as $key) {
            $data[$key] = $this->$key ?: '';
        }
        return $data;
    }

    /**
     * @return Entity | null
     */
    public function unchangedEntity()
    {
        return $this->_unchangedEntity;
    }

    public function beforeDelete()
    {
        $this->onBeforeDelete();
    }

    protected function onBeforeDelete()
    {
    }

    public function beforeInsert()
    {
        $this->onBeforeInsert();
    }

    protected function onBeforeInsert()
    {
    }

    public function beforeUpdate()
    {
        $this->onBeforeUpdate();
    }

    protected function onBeforeUpdate()
    {
    }

    public function beforeSave()
    {
        $this->onBeforeSave();
    }

    protected function onBeforeSave()
    {
    }

    public function afterDelete()
    {
        $this->onAfterDelete();
    }

    protected function onAfterDelete()
    {
    }

    public function afterInsert($id)
    {
        $primaryKey = $this->primaryKey();
        $this->$primaryKey = $id;
        $this->_isNew = false;

        $this->onAfterInsert();
    }

    protected function onAfterInsert()
    {
    }

    public function afterUpdate()
    {
        $this->onAfterUpdate();
    }

    protected function onAfterUpdate()
    {
    }

    public function afterSave()
    {
        $this->onAfterSave();
    }

    protected function onAfterSave()
    {
    }
}