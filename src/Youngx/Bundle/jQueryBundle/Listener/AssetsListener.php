<?php

namespace Youngx\Bundle\jQueryBundle\Listener;

use Youngx\Kernel\Container as Y;
use Youngx\Kernel\Event\GetElementEvent;
use Youngx\Kernel\Assets;
use Youngx\Kernel\Handler\ListenerRegistration;

class KernelListener implements ListenerRegistration
{

    public function registerCoreAssets(Assets $assets)
    {
        $assets->registerScripts('jQuery/jquery-1.7.2.js')
            ->registerScripts('jQuery/jquery.cookie.js');

        $assets->registerPackages('jquery.uniform');
    }

    public function datepickerPackage(Assets $assets)
    {
        $assets->registerScripts('jQuery/ui/jquery-ui-1.8.21.custom.min.js')
            ->registerStylesheets('jQuery/ui/jquery-ui-1.8.21.custom.css');
    }

    public function uniformPackage(Assets $assets)
    {
        $assets->registerScripts('jQuery/uniform/jquery.uniform.min.js')
            ->registerStylesheets('jQuery/uniform/uniform.default.css');
    }

    public function datepicker(GetElementEvent $event, array $attributes = array())
    {
        if (!$event->hasElement()) {
            Y::input('text', $event, $attributes);
            if ($event->hasElement()) {
                $text = $event->getElement();
                Y::assets()->registerPackages('jquery.ui.datepicker');

                if ($text->get('#datepicker') !== false) {
                    $options = $text->getConfig('#datepicker');
                    $options = $options ? json_encode($options) : '';

                    $code = <<<code
$('#{$text->get('id')}').datepicker({$options});
code;

                    Y::assets()->registerScriptCode($code);
                }
            }
        }

    }

    public static function registerListeners()
    {
        return array(
            'kernel.assets' => 'registerCoreAssets',
            'kernel.assets.package.jquery.ui.datepicker' => 'datepickerPackage',
            'kernel.input.datepicker' => 'datepicker',
            'kernel.assets.package.jquery.uniform' => 'uniformPackage'
        );
    }
}