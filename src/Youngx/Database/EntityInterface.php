<?php

namespace Youngx\Database;

interface EntityInterface
{
    public static function type();
    public static function table();
    public static function primaryKey();
    public static function fields();
}