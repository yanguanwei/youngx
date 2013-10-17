<?php

namespace Youngx\Bundle\ArchiveBundle\Entity;

use Youngx\Bundle\ChannelBundle\Entity\Channel;
use Youngx\Kernel\Database\Entity;
use Youngx\Kernel\Database\EntityBuilder;

class Archive extends Entity
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;

    public $id;
    public $channel_id;
    public $type;
    public $title;
    public $subtitle;
    public $link;
    public $cover;
    public $is_top;
    public $status;
    public $update_time;
    public $create_time;

    public static function type()
    {
        return 'archive';
    }

    public static function publishedScope(EntityBuilder $builder)
    {
        return $builder->where("{$builder->getAlias()}.status=?", self::STATUS_PUBLISHED);
    }

    public static function recentlyScope(EntityBuilder $builder, $limit = 5)
    {
        return $builder->order("{$builder->getAlias()}.update_time DESC")->limit($limit);
    }

    public static function joinChannelScope(EntityBuilder $builder, $alias = null)
    {
        return $builder->join('channel', $alias);
    }

    /**
     * @param int|array $cid
     * @param null $alias
     * @return EntityBuilder
     */
    public static function inChannels($cid, $alias = null)
    {
        $builder = self::build($alias);
        return $builder->where("{$builder->getAlias()}.cid IN (?)", $cid);
    }

    /**
     * @return \Youngx\Bundle\ChannelBundle\Entity\Channel
     */
    public function getChannel()
    {
        return $this->getJointField('channel');
    }

    public function setChannel(Channel $channel)
    {
        $this->setJointField('channel', $channel);
    }
}