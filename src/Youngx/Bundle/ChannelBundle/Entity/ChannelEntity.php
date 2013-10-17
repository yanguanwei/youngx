<?php

namespace Youngx\Bundle\ChannelBundle\Entity;

use Youngx\Database\Entity;
use Youngx\Database\Query;

class ChannelEvent extends Entity
{
    protected $id;
    protected $label;
    protected $parent_id = 0;
    protected $ancestor_id;
    protected $sort_num = 0;

    public static function type()
    {
        return 'channel';
    }

    public static function inParent(Query $query, $parentId)
    {
        $query->where("{$query->getAlias()}.parent_id IN (?)", $parentId);
    }

    /**
     * @return ChannelEntity
     */
    public function getParent()
    {
        return $this->resolveExtraFieldValue('parent');
    }


    public function hasParent()
    {
        return $this->parent_id != 0;
    }

    public static function table()
    {
        return 'y_channel';
    }

    public static function primaryKey()
    {
        return 'id';
    }

    public static function fields()
    {
        return array(
            'id', 'label', 'parent_id', 'sort_num'
        );
    }

    /**
     * @param mixed $ancestor_id
     */
    public function setAncestorId($ancestor_id)
    {
        $this->ancestor_id = $ancestor_id;
    }

    /**
     * @return mixed
     */
    public function getAncestorId()
    {
        return $this->ancestor_id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param int $parent_id
     */
    public function setParentId($parent_id)
    {
        $this->parent_id = $parent_id;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * @param int $sort_num
     */
    public function setSortNum($sort_num)
    {
        $this->sort_num = $sort_num;
    }

    /**
     * @return int
     */
    public function getSortNum()
    {
        return $this->sort_num;
    }
}