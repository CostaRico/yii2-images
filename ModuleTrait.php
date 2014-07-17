<?php
/**
 * Created by PhpStorm.
 * User: kostanevazno
 * Date: 17.07.14
 * Time: 0:20
 */

namespace rico\yii2images;


trait ModuleTrait
{
    /**
     * @var null|\rico\yii2images\Module
     */
    private $_module;

    /**
     * @return null|\rico\yii2images\Module
     */
    protected function getModule()
    {
        if ($this->_module == null) {
            $this->_module = \Yii::$app->getModule('yii2images');
        }

        return $this->_module;
    }
}