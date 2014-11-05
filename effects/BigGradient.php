<?php
/**
 * Created by PhpStorm.
 * User: costa
 * Date: 05.11.14
 * Time: 14:22
 */

namespace rico\yii2images\effects;
use rico\yii2images\effects\Gradient;


class BigGradient extends Gradient {
    public $coverPercent = 50;
    public static function getId()
    {
        return 'BigGradientFromTop';
    }
}