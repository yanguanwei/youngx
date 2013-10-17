<?php

namespace Youngx\Bundle\CategoryBundle\Entity;

use Youngx\Kernel\Database\Entity;

class Category extends Entity
{
    public $id;
    public $label;
    public $name;
    public $parent_id;
    public $sort_num;

    public static function type()
    {
        return 'category';
    }
}