<?php
/**
 * Created by PhpStorm.
 * User: kostanevazno
 * Date: 05.08.14
 * Time: 18:21
 *
 * TODO: check that placeholder is enable in module class
 * override methods
 */

namespace rico\yii2images\models;


class PlaceHolder extends Image {
    public function getUrl($size = false){
        $url = $this->getModule()->placeHolderUrl;
        if(!$url){
            throw new \Exception('PlaceHolder image must have url setting!!!');
        }
        return $url;
    }

    public function getPathToOrigin(){

        $url = $this->getModule()->placeHolderPath;
        if(!$url){
            throw new \Exception('PlaceHolder image must have path setting!!!');
        }
        return $url;
    }
} 