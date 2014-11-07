<?php
/**
 * Created by PhpStorm.
 * User: costa
 * Date: 06.11.14
 * Time: 14:59
 */
namespace rico\yii2images\widgets;
use yii\base\Widget;
use yii\helpers\Html;


class FormLoader extends Widget {
    public $model;
    public $urlAddImage;
    public $urlRemoveImage;
    public $urlSetMainImage;
    public $form;

    public function run()
    {

        return $this->render('formLoader',
            [
                'model'=>$this->model,
                'form'=>$this->form,
                'urls' => [
                    'addImage' => $this->urlAddImage,
                    'removeImage' => $this->urlRemoveImage,
                    'setMain' => $this->urlSetMainImage
                ]
            ]);
    }


}