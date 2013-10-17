<?php

namespace Youngx\Util\Image;

class ImageResource
{
    protected $file;
    protected $width;
    protected $height;
    protected $type;
    protected $resource;

    public function __construct($file)
    {
        list($width, $height, $type) = getimagesize($file);

        switch ($type) { //判断图像类型
            case 1 :
                $resource = imagecreatefromgif($file); //从大图创建GIF图像
                break;
            case 2 :
                $resource = imagecreatefromjpeg($file); //从大图创建JPG图像
                break;
            case 3 :
                $resource = imagecreatefrompng($file); //从大图创建PNG图像
                break;
            default:
                throw new \RuntimeException('unsupported image file: ' . $file);
        }

        $this->width = $width;
        $this->height = $height;
        $this->type = $type;
        $this->resource = $resource;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }
}