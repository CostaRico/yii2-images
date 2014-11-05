<?php
/**
 * Created by PhpStorm.
 * User: costa
 * Date: 30.10.14
 * Time: 16:59
 */
namespace rico\yii2images\effects;

class GradientFromTop {

    const HORIZONT_DIRECTION = 'h';
    const VERTICAL_DIRECTION = 'v';

    public $direction = self::HORIZONT_DIRECTION;
    public $coverPercent = 20;
    public $fromColor = 'transparent';
    public $toColor = 'black';

    public static function getId()
    {
        return 'SimpleGradientFromTop';
    }

    public function applyTo($im)
    {
        $gradient = new \Imagick();

        $gradient->newPseudoImage($im->getImageWidth(), $im->getImageHeight()*$this->coverPercent/100, "gradient:".$this->toColor."-".$this->fromColor);
        $im->compositeImage($gradient, \Imagick::COMPOSITE_OVER, 0, 0);

        return $im;
    }

}