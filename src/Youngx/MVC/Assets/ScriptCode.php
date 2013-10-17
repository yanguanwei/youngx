<?php

namespace Youngx\MVC\Assets;

class ScriptCode extends Code
{
    const POSITION_READY = 0;
    const POSITION_HEAD = 1;
    const POSITION_FOOT = 2;

    private $position = self::POSITION_READY;

    public function getPosition()
    {
        return $this->position;
    }

    public function setReadyPosition()
    {
        $this->position = self::POSITION_READY;

        return $this;
    }

    public function setHeadPosition()
    {
        $this->position = self::POSITION_HEAD;

        return $this;
    }

    public function setFootPosition()
    {
        $this->position = self::POSITION_FOOT;

        return $this;
    }

    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    public function isReadyPosition()
    {
        return $this->position === self::POSITION_READY;
    }

    public function isHeadPosition()
    {
        return $this->position === self::POSITION_HEAD;
    }

    public function isFootPosition()
    {
        return $this->position === self::POSITION_FOOT;
    }

    public function toString()
    {
        $codes = parent::toString();
        if ($this->isReadyPosition()) {
            $codes = "(function($) {\n$(function(){\n{$codes}\n});})(jQuery);";
        }
        return $codes;
    }
}