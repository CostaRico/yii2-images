<?php
/**
 * Created by PhpStorm.
 * User: kostanevazno
 * Date: 22.06.14
 * Time: 16:58
 */

namespace rico\yii2images\behaviors;


use rico\yii2images\models\Image;

use yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use rico\yii2images\models;
use yii\helpers\BaseFileHelper;
use \rico\yii2images\ModuleTrait;



class ImageBehave extends Behavior
{

    use ModuleTrait;
    public $createAliasMethod = false;



    /**
     *
     * Method copies image file to module store and creates db record.
     *
     * @param $absolutePath
     * @param bool $isFirst
     * @return bool|Image
     * @throws \Exception
     */
    public function attachImage($absolutePath, $isMain = false)
    {
        if(!preg_match('#http#', $absolutePath)){
            if (!file_exists($absolutePath)) {
                throw new \Exception('File not exist! :'.$absolutePath);
            }
        }else{
            //nothing
        }

        if (!$this->owner->id) {
            throw new \Exception('Owner must have id when you attach image!');
        }

        $pictureFileName =
            substr(md5(microtime(true) . $absolutePath), 4, 6)
            . '.' .
            pathinfo($absolutePath, PATHINFO_EXTENSION);
        $pictureSubDir = $this->getModule()->getModelSubDir($this->owner);
        $storePath = $this->getModule()->getStorePath($this->owner);

        $newAbsolutePath = $storePath .
            DIRECTORY_SEPARATOR . $pictureSubDir .
            DIRECTORY_SEPARATOR . $pictureFileName;

        BaseFileHelper::createDirectory($storePath . DIRECTORY_SEPARATOR . $pictureSubDir,
            0775, true);

        copy($absolutePath, $newAbsolutePath);

        if (!file_exists($absolutePath)) {
            throw new \Exception('Cant copy file! ' . $absolutePath . ' to ' . $newAbsolutePath);
        }


        $image = new models\Image;
        $image->itemId = $this->owner->id;
        $image->filePath = $pictureSubDir . '/' . $pictureFileName;
        $image->modelName = $this->getModule()->getShortClass($this->owner);


        $image->urlAlias = $this->getAlias($image);

        if(!$image->save()){
            return false;
        }

        if (count($image->getErrors()) > 0) {

            $ar = array_shift($image->getErrors());

            unlink($newAbsolutePath);
            throw new \Exception(array_shift($ar));
        }

        //If main image not exists
        if(!$this->owner->getImage() or $isMain){
            $this->setMainImage($image);
        }


        return $image;
    }

    /**
     * Sets main image of model
     * @param $img
     * @throws \Exception
     */
    public function setMainImage($img)
    {
        if ($this->owner->id != $img->itemId) {
            throw new \Exception('Image must belong to this model');
        }
        $counter = 1;
        /* @var $img Image */
        $img->setMain(true);
        $img->urlAlias = $this->getAliasString() . '-' . $counter;
        $img->save();


        $images = $this->owner->getImages();
        foreach ($images as $allImg) {

            if ($allImg->id == $img->id) {
                continue;
            } else {
                $counter++;
            }

            $allImg->setMain(false);
            $allImg->urlAlias = $this->getAliasString() . '-' . $counter;
            $allImg->save();
        }

        $this->owner->clearImagesCache();
    }


    /**
     * Clear all images cache (and resized copies)
     * @return bool
     */
    public function clearImagesCache()
    {
        $cachePath = $this->getModule()->getCachePath();
        $subdir = $this->getModule()->getModelSubDir($this->owner);

        $dirToRemove = $cachePath . '/' . $subdir;

        if (preg_match('/' . preg_quote($cachePath, '/') . '/', $dirToRemove)) {
            BaseFileHelper::removeDirectory($dirToRemove);
            //exec('rm -rf ' . $dirToRemove);
            return true;
        } else {
            return false;
        }
    }


    /**
     * Returns model images
     * First image alwats must be main image
     * @return array|yii\db\ActiveRecord[]
     */
    public function getImages()
    {
        $finder = $this->getImagesFinder();

        $imageQuery = Image::find()
            ->where($finder);
        $imageQuery->orderBy(['isMain' => SORT_DESC, 'id' => SORT_ASC]);

        $imageRecords = $imageQuery->all();
        if(!$imageRecords){
            return [];
        }
        return $imageRecords;
    }


    /**
     * returns main model image
     * @return array|null|ActiveRecord
     */
    public function getImage()
    {
        $finder = $this->getImagesFinder(['isMain' => 1]);
        $imageQuery = Image::find()
            ->where($finder);
        $imageQuery->orderBy(['isMain' => SORT_DESC, 'id' => SORT_ASC]);

        $img = $imageQuery->one();
        if(!$img){
            return null;
        }

        return $img;
    }

    /**
     * Remove all model images
     */
    public function removeImages()
    {
        $images = $this->owner->getImages();
        if (count($images) < 1) {
            return true;
        } else {
            foreach ($images as $image) {
                $this->owner->removeImage($image);
            }
        }
    }


    /**
     *
     * removes concrete model's image
     * @param Image $img
     * @throws \Exception
     */
    public function removeImage(Image $img)
    {
        $img->clearCache();

        $storePath = $this->getModule()->getStorePath();

        $fileToRemove = $storePath . DIRECTORY_SEPARATOR . $img->filePath;
        if (preg_match('@\.@', $fileToRemove) and is_file($fileToRemove)) {
            unlink($fileToRemove);
        }
        $img->delete();
    }

    private function getImagesFinder($additionWhere = false)
    {
        $base = [
            'itemId' => $this->owner->id,
            'modelName' => $this->getModule()->getShortClass($this->owner)
        ];

        if ($additionWhere) {
            $base = \yii\helpers\BaseArrayHelper::merge($base, $additionWhere);
        }

        return $base;
    }



    /** Make string part of image's url
     * @return string
     * @throws \Exception
     */
    private function getAliasString()
    {
        if ($this->createAliasMethod) {
            $string = $this->owner->{$this->createAliasMethod}();
            if (!is_string($string)) {
                throw new \Exception("Image's url must be string!");
            } else {
                return $this->owner->{$this->createAliasMethod}();
            }

        } else {
            return substr(md5(microtime()), 0, 10);
        }
    }


    /**
     *
     * Обновить алиасы для картинок
     * Зачистить кэш
     */
    private function getAlias()
    {
        $aliasWords = $this->getAliasString();
        $imagesCount = count($this->owner->getImages());

        return $aliasWords . '-' . intval($imagesCount + 1);
    }


}


