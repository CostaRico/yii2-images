<?php
/**
 * Created by PhpStorm.
 * User: costa
 * Date: 30.10.14
 * Time: 16:59
 */
namespace rico\yii2images\effects;

class Gradient {

    const HORIZONT_DIRECTION = 'h';
    const VERTICAL_DIRECTION = 'v';

    public $direction = self::HORIZONT_DIRECTION;
    public $coverPercent = 20;
    public $fromColor = 'transparent';
    public $toColor = 'black';

    public static function getId()
    {
        /*$r = new \ReflectionClass(__CLASS__);
        $className = $r->getShortName();
        return $className.substr(md5($this->fromColor.$this->toColor.$this->direction.$this->coverPercent), 0, 6);*/
        return 'SimpleGradient';
    }

    public function applyTo($im)
    {

        /* Создаём градиент. Это будет наложением для отражения */
        $gradient = new \Imagick();

        /* Градиент должен быть достаточно большой для изображения и его рамки */
        $gradient->newPseudoImage($im->getImageWidth(), $im->getImageHeight()*$this->coverPercent/100, "gradient:".$this->fromColor."-".$this->toColor);
        $im->compositeImage($gradient, \Imagick::COMPOSITE_OVER, 0, $im->getImageHeight()-$gradient->getImageHeight()  );

        return $im;
    }

}