<?php
/**
 * Created by PhpStorm.
 * User: costa
 * Date: 06.11.14
 * Time: 15:32
 */

namespace rico\yii2images;
use yii\web\AssetBundle;


class Asset extends AssetBundle{
    public $sourcePath = '@app/vendor/costa-rico/yii2-images/widgets/views';
    public $js = [
        'imagesLoader.js'
    ];
    public $css = [
      'style.css'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
