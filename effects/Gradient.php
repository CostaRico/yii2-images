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
    public $coverPercent = 66;
    public $fromColor = 'transparent';
    public $toColor = 'black';

    public function getUniqueCode()
    {
        return __CLASS__.md5($this->fromColor.$this->toColor.$this->direction.$this->coverPercent);
    }

    public function applyTo($absolutePath)
    {
        $im = new \Imagick($absolutePath);

        /* Создаём градиент. Это будет наложением для отражения */
        $gradient = new \Imagick();

        /* Градиент должен быть достаточно большой для изображения и его рамки */
        $gradient->newPseudoImage($im->getImageWidth(), $im->getImageHeight()*0.66, "gradient:transparent-#abc123");
        $im->compositeImage($gradient, \Imagick::COMPOSITE_OVER, 0, $im->getImageHeight()-$gradient->getImageHeight()  );

        return $im;
    }

}