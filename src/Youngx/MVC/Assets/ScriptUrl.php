<?php

namespace Youngx\MVC\Assets;

class ScriptUrl extends UrlAsset
{
    const POSITION_HEAD = 1;
    const POSITION_FOOT = 2;

    private $position = self::POSITION_FOOT;

    public function getPosition()
    {
        return $this->position;
    }

    public function setFootPosition()
    {
        $this->position = self::POSITION_FOOT;

        return $this;
    }

    public function setHeadPosition()
    {
        $this->position = self::POSITION_HEAD;

        return $this;
    }

    public function isHeadPosition()
    {
        return $this->position === self::POSITION_HEAD;
    }

    public function isFootPosition()
    {
        return $this->position === self::POSITION_FOOT;
    }
}