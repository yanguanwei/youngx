<?php

namespace Youngx\Util\Captcha;

class Captcha
{
    private $namespace = 'young_util_captcha';
    private $expire = 3600;
    private $fontType = 'en/sf_hollywood_hills.ttf';
    private $fontSize = 14;
    private $width = null;
    private $height = null;
    private $length = 4;
    private $bgRGB = array(243, 251, 254);
    private $code;

    /**
     * 验证码中使用的字符，01IO容易混淆，建议不用
     *
     * @var string
     */
    private $codes = '346789ABCDEFGHJKLMNPQRTUVWXY';

    /**
     * 是否添加混淆曲线
     *
     * @var boolean
     */
    private $isCurve = false;
    /**
     * 是否添加杂点
     *
     * @var boolean
     */
    private $isNoise = true;

    private $background;

    /**
     * 初始化验证码
     *
     * @param string $id 存储名称
     */
    public function __construct($id = null)
    {
        if (null !== $id) {
            $this->namespace .= "_{$id}";
        }
    }

    /**
     * 验证码是否正确
     *
     * @param $code
     * @return boolean
     */
    public function isValid($code)
    {
        if (!$_COOKIE[$this->namespace] || $_COOKIE[$this->namespace] !== $this->encode($code)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 开始生成验证码图片
     */
    public function create($output = true)
    {
        $width = $this->getWidth();
        $height = $this->getHeight();

        $image = imagecreate($width, $height);
        imagecolorallocate($image, $this->bgRGB[0], $this->bgRGB[1], $this->bgRGB[2]);

        // 验证码字体随机颜色
        $color = imagecolorallocate($image, mt_rand(1, 120), mt_rand(1, 120), mt_rand(1, 120));

        $ttf = $this->getFontBasePath();

        if ($this->isNoise) {
            $this->makeNoise($image);
        }

        if ($this->isCurve) {
            $this->makeCurve($image, $color);
        }

        if ($this->background) {
            $this->makeBackground($image, $this->background);
        }

        $this->code = $this->generateCode($image, $color, $ttf);

        header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header("content-type: image/png");

        if ($output) {
            ob_start();
        }
        // 输出图像
        imagepng($image);
        imagedestroy($image);

        if ($output) {
            return ob_get_clean();
        }
    }

    public function getCode()
    {
        return $this->code;
    }

    /**
     * 设置验证码图片的大小
     *
     * @param int $width 宽度
     * @param int $height 高度
     * @return $this
     */
    public function setSize($width, $height)
    {
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * 设置是否使用背景图片
     *
     * @param boolean $background
     * @return $this
     */
    public function background($background)
    {
        $this->background = (bool)$background;

        return $this;
    }

    /**
     * 设置是否添加杂点
     *
     * @param boolean $isNoise
     * @return $this
     */
    public function noise($isNoise)
    {
        $this->isNoise = (bool)$isNoise;

        return $this;
    }

    /**
     * 设置是否添加混淆曲线
     *
     * @param boolean $isCurve
     * @return $this
     */
    public function curve($isCurve)
    {
        $this->isCurve = (bool)$isCurve;

        return $this;
    }

    /**
     * 设置字体类型
     *
     * @param string $fontType 可取值：en、cn
     * @return $this
     */
    public function setFontType($fontType)
    {
        $this->fontType = $fontType;

        return $this;
    }

    /**
     * 设置字体大小
     *
     * @param int $fontSize
     * @return $this
     */
    public function setFontSize($fontSize)
    {
        $this->fontSize = $fontSize;

        return $this;
    }

    /**
     * 设置背景颜色
     *
     * @param int $r
     * @param int $g
     * @param int $b
     * @return $this
     */
    public function setBgRGB($r, $g, $b)
    {
        $this->bgRGB = array(
            $r,
            $g,
            $b
        );

        return $this;
    }

    private function generateCode($image, $color, $ttf)
    {
        $width = $this->getWidth();
        $height = $this->getHeight();

        $code = array(); // 验证码
        $nx = $width / $this->length;
        $nxb = ($nx - $this->fontSize) / 2; // 验证码第N个字符的左边距
        $fontType = substr($this->fontType, 0, 2);
        for ($i = 0; $i < $this->length; $i++) {
            if ('cn' == $fontType) {
                $code[$i] = chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE));
            } else {
                $code[$i] = $this->codes[mt_rand(0, 27)];
                $angle = 0; //mt_rand(-20, 20);
                //$x += mt_rand($this->_fontSize * 1.2, $this->_fontSize * 1.6);
                $x = $nx * $i + $nxb;
                //$y = $this->_fontSize * 1.5;
                $y = $height / 2 + $this->fontSize / 2;
                imagettftext($image, $this->fontSize, $angle, $x, $y, $color, $ttf, $code[$i]);
            }
        }

        $code = implode('', $code);
        setcookie($this->namespace, $this->encode($code), time() + $this->expire, '/');

        if ('cn' == $fontType) {
            imagettftext(
                $image,
                $this->fontSize,
                0,
                ($this->getWidth() - $this->fontSize * $this->length * 1.2) / 3,
                $this->fontSize * 1.5,
                $color,
                $ttf,
                iconv("GB2312", "UTF-8", implode('', $code))
            );
        }

        return $code;
    }

    private function encode($code)
    {
        return md5(strtoupper($code));
    }

    private function getWidth()
    {
        if (null == $this->width) {
            $this->width = $this->length * $this->fontSize * 1.5;
        }

        return $this->width;
    }

    private function getHeight()
    {
        if (null == $this->height) {
            $this->height = $this->fontSize * 1.8;
        }

        return $this->height;
    }

    private function getFontBasePath()
    {
        return __DIR__ . "/fonts/{$this->fontType}";
    }

    /**
     * 画杂点
     * 往图片上写不同颜色的字母或数字
     */
    private function makeNoise($image)
    {
        for ($i = 0; $i < 10; $i++) {
            //杂点颜色
            $noiseColor = imagecolorallocate($image, mt_rand(150, 225), mt_rand(150, 225), mt_rand(150, 225));
            for ($j = 0; $j < 5; $j++) {
                // 绘杂点// 杂点文本为随机的字母或数字
                imagestring(
                    $image,
                    5,
                    mt_rand(-10, $this->getWidth()),
                    mt_rand(-10, $this->getHeight()),
                    $this->codes[mt_rand(0, 27)],
                    $noiseColor
                );
            }
        }
    }

    /**
     * 画一条由两条连在一起构成的随机正弦函数曲线作干扰线(你可以改成更帅的曲线函数)
     *
     *      高中的数学公式
     *        正弦型函数解析式：y=Asin(ωx+φ)+b
     *      各常数值对函数图像的影响：
     *        A：决定峰值（即纵向拉伸压缩的倍数）
     *        b：表示波形在Y轴的位置关系或纵向移动距离（上加下减）
     *        φ：决定波形与X轴位置关系或横向移动距离（左加右减）
     *        ω：决定周期（最小正周期T=2π/∣ω∣）
     *
     */
    private function makeCurve($image, $color)
    {
        $px = $py = 0;

        $height = $this->getHeight();
        $width = $this->getWidth();

        // 曲线前部分
        $A = mt_rand(1, $height / 2); // 振幅
        $b = mt_rand(-$height / 4, $height / 4); // Y轴方向偏移量
        $f = mt_rand(-$height / 4, $height / 4); // X轴方向偏移量
        $T = mt_rand($height, $width * 2); // 周期
        $w = (2 * M_PI) / $T;

        $px1 = 0; // 曲线横坐标起始位置
        $px2 = mt_rand($width / 2, $width * 0.8); // 曲线横坐标结束位置

        for ($px = $px1; $px <= $px2; $px = $px + 0.9) {
            if ($w != 0) {
                $py = $A * sin($w * $px + $f) + $b + $height / 2; // y = Asin(ωx+φ) + b
                $i = (int)($this->fontSize / 8);
                while ($i > 0) {
                    imagesetpixel(
                        $image,
                        $px,
                        $py + $i,
                        $color
                    ); // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多
                    $i--;
                }
            }
        }

        // 曲线后部分
        $A = mt_rand(1, $height / 2); // 振幅
        $f = mt_rand(-$height / 4, $height / 4); // X轴方向偏移量
        $T = mt_rand($height, $width * 2); // 周期
        $w = (2 * M_PI) / $T;
        $b = $py - $A * sin($w * $px + $f) - $height / 2;
        $px1 = $px2;
        $px2 = $width;

        for ($px = $px1; $px <= $px2; $px = $px + 0.9) {
            if ($w != 0) {
                $py = $A * sin($w * $px + $f) + $b + $height / 2; // y = Asin(ωx+φ) + b
                $i = (int)($this->fontSize / 8);
                while ($i > 0) {
                    imagesetpixel($image, $px, $py + $i, $color);
                    $i--;
                }
            }
        }
    }

    /**
     * 绘制背景图片
     * 注：如果验证码输出图片比较大，将占用比较多的系统资源
     */
    private function makeBackground($image, $background)
    {
        $gb = __DIR__ . "/bgs/{$background}";
        if (is_file($gb)) {
            list($width, $height) = @getimagesize($gb);
            $bgImage = @imagecreatefromjpeg($gb);
            @imagecopyresampled($image, $bgImage, 0, 0, 0, 0, $this->getWidth(), $this->getHeight(), $width, $height);
            @imagedestroy($bgImage);
        }
    }
}
