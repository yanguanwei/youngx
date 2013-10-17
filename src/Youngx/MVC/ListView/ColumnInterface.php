<?php

namespace Youngx\MVC\ListView;

use Youngx\Database\Entity;
use Youngx\MVC\Context;
use Youngx\MVC\Html;

interface ColumnInterface
{
    public function getName();
    public function getLabel();
    public function isSortable();
    public function format(Context $context, Entity $entity, Html $html);
    public function sortable($sortable = true);
}