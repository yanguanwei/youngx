<?php

namespace Youngx\MVC;

use Youngx\MVC\Assets\ScriptCode;
use Youngx\MVC\Assets\ScriptUrl;
use Youngx\MVC\Assets\StyleCode;
use Youngx\MVC\Assets\StyleUrl;
use Youngx\MVC\Html;
use Youngx\EventHandler\Event\GetSortableArrayEvent;
use Youngx\EventHandler\Registration;
use Youngx\Util\SortableArray;

class Assets implements Registration
{
    const SCRIPT_POS_READY = 0;
    const SCRIPT_POS_HEAD = 1;
    const SCRIPT_POS_FOOT = 2;

    private $packages = array();
    /**
     * @var SortableArray
     */
    private $scriptUrlAssets;
    /**
     * @var SortableArray
     */
    private $styleUrlAssets;
    /**
     * @var SortableArray
     */
    private $styleCodeAssets;
    /**
     * @var SortableArray
     */
    private $scriptCodeAssets;

    protected $handler;
    /**
     * @var Context
     */
    protected $context;

    public function __construct(Handler $handler)
    {
        $this->handler = $handler;

        $handler->triggerWithMenu('kernel.assets', $this);
    }

    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param string $key
     * @return StyleCode | null
     */
    public function getStyleCode($key)
    {
        return $this->styleCodeAssets ? $this->scriptCodeAssets->get($key) : null;
    }

    /**
     * @param string $key
     * @return ScriptCode | null
     */
    public function getScriptCode($key)
    {
        return $this->scriptCodeAssets ? $this->scriptCodeAssets->get($key) : null;
    }

    /**
     * @param string $key
     * @return ScriptUrl | null
     */
    public function getScriptUrl($key)
    {
        return $this->scriptUrlAssets ? $this->scriptUrlAssets->get($key) : null;
    }

    /**
     * @param string $key
     * @return StyleUrl | null
     */
    public function getStyleUrl($key)
    {
        return $this->styleUrlAssets ? $this->styleUrlAssets->get($key) : null;
    }

    public function registerPackage($package, $version = null)
    {
        if (!isset($this->packages[$package])) {
            $this->handler->trigger("kernel.assets.package#{$package}", $this, $version);
            $this->packages[$package] = true;
        }

        return $this;
    }

    /**
     * @param string $key
     * @param string | array $url
     * @param int $sort
     * @return StyleUrl
     */
    public function registerStyleUrl($key, $url, $sort = 0)
    {
        if (!$this->getStyleUrlAssets()->has($key)) {
            $this->getStyleUrlAssets()->set($key, new StyleUrl($url), $sort);
        }
        return $this->getStyleUrlAssets()->get($key);
    }

    /**
     * @param string $key
     * @param string $code
     * @param int $sort
     * @return StyleCode
     */
    public function registerStyleCode($key, $code, $sort = 0)
    {
        $this->getStyleCodeAssets()->set($key, $asset = new StyleCode($key, $code), $sort);

        return $asset;
    }

    /**
     * @param $key
     * @param $url
     * @param int $sort
     * @return ScriptUrl
     */
    public function registerScriptUrl($key, $url, $sort = 0)
    {
        if (!$this->getScriptUrlAssets()->has($key)) {
            $this->getScriptUrlAssets()->set($key,  new ScriptUrl($url), $sort);
        }
        return $this->getScriptUrlAssets()->get($key);
    }

    /**
     * @param $key
     * @param $code
     * @param int $sort
     * @return ScriptCode
     */
    public function registerScriptCode($key, $code, $sort = 0)
    {
        $this->getScriptCodeAssets()->set($key, $asset = new ScriptCode($key, $code), $sort);

        return $asset;
    }

    public function url($path)
    {
        if (!preg_match('/^http[s]*:\/\//', $path) && $path[0] !== '/') {
            $path = '/assets/' . $path;
        }
        return $path;
    }

    private function parseStyleUrl(StyleUrl $asset)
    {
        $asset->setUrl($this->url($asset->getUrl()));
        return $this->context->html('stylesheet', $asset->getAttributes());
    }

    private function parseScriptUrl(ScriptUrl $asset)
    {
        $asset->setUrl($this->url($asset->getUrl()));
        return $this->context->html('script', $asset->getAttributes());
    }

    public function renderHeadBlock(GetSortableArrayEvent $event)
    {
        if ($this->styleUrlAssets) {
            $assets = array();
            foreach ($this->styleUrlAssets->all() as $asset) {
                $assets[] = $this->parseStyleUrl($asset);
            }
            $event->set('styleUrls', implode("\n", $assets));
        }

        if ($this->scriptUrlAssets) {
            $assets = array();
            foreach ($this->scriptUrlAssets->all() as $asset) {
                if ($asset->isHeadPosition()) {
                    $assets[] = $this->parseScriptUrl($asset);
                }
            }
            $event->set('scriptUrls', implode("\n", $assets));
        }

        if ($this->styleCodeAssets) {
            $event->set('styleCodes', $this->context->html('style', array(
                        '#content' => implode("\n", $this->styleCodeAssets->all())
                    )));
        }

        if ($this->scriptCodeAssets) {
            $assets = array();
            foreach ($this->scriptCodeAssets->all() as $asset) {
                if ($asset->isHeadPosition()) {
                    $assets[] = $this->context->html('script', array('#content' => $asset));
                }
            }
            if ($assets) {
                $event->set('scriptCodes', implode("\n", $assets));
            }
        }

    }

    public function renderBodyBlock(GetSortableArrayEvent $event)
    {
        if ($this->scriptUrlAssets) {
            $assets = array();
            foreach ($this->scriptUrlAssets->all() as $asset) {
                if ($asset->isFootPosition()) {
                    $assets[] = $this->parseScriptUrl($asset);
                }
            }
            $event->set('scriptUrls', implode("\n", $assets));
        }

        if ($this->scriptCodeAssets) {
            $assets = array();
            foreach ($this->scriptCodeAssets->all() as $asset) {
                if ($asset->isFootPosition()) {
                    $assets[] = $this->context->html('script', array('#content' => $asset));
                }
            }
            if ($assets) {
                $event->set('scriptFootCodes', implode("\n", $assets));
            }

            $assets = array();;
            foreach ($this->scriptCodeAssets->all() as $asset) {
                if ($asset->isReadyPosition()) {
                    $assets[] = $this->context->html('script', array('#content' => $asset));
                }
            }
            if ($assets) {
                $event->set('scriptReadyCodes', implode("\n", $assets));
            }
        }
    }

    /**
     * @return SortableArray
     */
    protected function getScriptCodeAssets()
    {
        if (null === $this->scriptCodeAssets) {
            $this->scriptCodeAssets = new SortableArray();
        }
        return $this->scriptCodeAssets;
    }

    /**
     * @return SortableArray
     */
    protected function getStyleCodeAssets()
    {
        if (null === $this->styleCodeAssets) {
            $this->styleCodeAssets = new SortableArray();
        }
        return $this->styleCodeAssets;
    }

    /**
     * @return SortableArray
     */
    protected function getStyleUrlAssets()
    {
        if (null === $this->styleUrlAssets) {
            $this->styleUrlAssets = new SortableArray();
        }
        return $this->styleUrlAssets;
    }

    /**
     * @return SortableArray
     */
    protected function getScriptUrlAssets()
    {
        if (null === $this->scriptUrlAssets) {
            $this->scriptUrlAssets = new SortableArray();
        }
        return $this->scriptUrlAssets;
    }

    public static function registerListeners()
    {
        return array(
            'kernel.block#head' => array('renderHeadBlock', 1024),
            'kernel.block#body' => array('renderBodyBlock', 1024)
        );
    }
}