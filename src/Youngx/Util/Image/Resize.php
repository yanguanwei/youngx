<?php

namespace Youngx\Util\Image;

class Resize
{
    public static function fixedResize($srcFile, $destFile, $width, $height)
    {
        $srcImg = new ImageResource($srcFile);

        $sourceWidth = $srcImg->getWidth();
        $sourceHeight = $srcImg->getHeight();

        $widthRatio = 1.0 * $width / $sourceWidth;
        $heightRatio = 1.0 * $height / $sourceHeight;
        $ratio = 1.0;
        // 生成的图像的高宽比原来的都小，或都大 ，原则是 取大比例放大，取大比例缩小（缩小的比例就比较小了）
        if (($widthRatio < 1 && $heightRatio < 1) || ($widthRatio > 1 && $heightRatio > 1)) {
            if ($widthRatio < $heightRatio) {
                $ratio = $heightRatio; // 情况一，宽度的比例比高度方向的小，按照高度的比例标准来裁剪或放大
            } else {
                $ratio = $widthRatio;
            }
            // 定义一个中间的临时图像，该图像的宽高比 正好满足目标要求
            $interWidth = (int)($width / $ratio);
            $interHeight = (int)($height / $ratio);
            $interImage = imagecreatetruecolor($interWidth, $interHeight);
            imagecopy($interImage, $srcImg->getResource(), 0, 0, 0, 0, $interWidth, $interHeight);
            // 生成一个以最大边长度为大小的是目标图像$ratio比例的临时图像
            // 定义一个新的图像
            $destinationImage = imagecreatetruecolor($width, $height);
            imagecopyresampled($destinationImage, $interImage, 0, 0, 0, 0, $width, $height, $interWidth, $interHeight);
        } else {
            // 2 目标图像 的一个边大于原图，一个边小于原图 ，先放大平普图像，然后裁剪
            // =if( ($ratio_w < 1 && $ratio_h > 1) || ($ratio_w >1 && $ratio_h <1) )
            $ratio = $heightRatio > $widthRatio ? $heightRatio : $widthRatio; //取比例大的那个值
            // 定义一个中间的大图像，该图像的高或宽和目标图像相等，然后对原图放大
            $interWidth = (int)($sourceWidth * $ratio);
            $interHeight = (int)($sourceHeight * $ratio);
            $interImage = imagecreatetruecolor($interWidth, $interHeight);
            //将原图缩放比例后裁剪
            imagecopyresampled(
                $interImage,
                $srcImg->getResource(),
                0,
                0,
                0,
                0,
                $interWidth,
                $interHeight,
                $sourceWidth,
                $sourceHeight
            );
            // 定义一个新的图像
            $destinationImage = imagecreatetruecolor($width, $height);
            imagecopy($destinationImage, $interImage, 0, 0, 0, 0, $width, $height);
        }
        self::output($srcImg->getType(), $destinationImage, $destFile);
    }

    public static function maxResize($srcFile, $destFile, $size, $type = null)
    {
        $srcImg = new ImageResource($srcFile);

        $width = $srcImg->getWidth();
        $height = $srcImg->getHeight();

        if (null !== $type) {
            if ('width' == $type) {
                $height *= ($size / $width);
                $width = $size;
            } else {
                if ('height' == $type) {
                    $width *= ($size / $height);
                    $height = $size;
                }
            }
        } else {
            if ($width > $size || $height > $size) {
                if ($width > $height) {
                    $height *= ($size / $width);
                    $width = $size;
                } else {
                    $width *= ($size / $height);
                    $height = $size;
                }
            }
        }

        $width = (int) $width;
        $height = (int) $height;

        $destImg = imagecreatetruecolor($width, $height);
        imagecopyresized($destImg, $srcImg->getResource(), 0, 0, 0, 0, $width, $height, $srcImg->getWidth(), $srcImg->getHeight());
        self::output($srcImg->getType(), $destImg, $destFile);
    }

    protected static function output($type, $image, $destFile)
    {
        switch ($type) { //判断图像类型
            case 1 :
                imagegif($image, $destFile, 100);
                break;
            case 2 :
                imagejpeg($image, $destFile, 100);
                break;
            case 3 :
                imagepng($image, $destFile, 100);
                break;
        }
    }
}