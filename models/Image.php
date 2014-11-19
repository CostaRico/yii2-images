<?php


/**
 * This is the model class for table "image".
 *
 * @property integer $id
 * @property string $filePath
 * @property integer $itemId
 * @property integer $isMain
 * @property string $modelName
 * @property string $urlAlias
 */

namespace rico\yii2images\models;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\helpers\BaseFileHelper;
use \rico\yii2images\ModuleTrait;
use rico\yii2images\effects\Gradient;
use rico\yii2images\effects\BigGradient;
use rico\yii2images\effects\GradientFromTop;



class Image extends \yii\db\ActiveRecord
{
    use ModuleTrait;


    private $helper = false;


    protected $effects = [];


    public function setGradient($fromBottom = true, $coverPercent = false)
    {
        if($fromBottom){
            $gradient = new Gradient;
            $this->effects[] = $gradient;
        }else{
            $gradient = new GradientFromTop;
            $this->effects[] = $gradient;
        }

        return $this;
    }

    public function setBigGradient($fromBottom = true, $coverPercent = false)
    {
        $gradient = new BigGradient;
        $this->effects[] = $gradient;
        return $this;
    }


    public function clearCache(){
        $subDir = $this->getSubDur();

        $dirToRemove = $this->getModule()->getCachePath().DIRECTORY_SEPARATOR.$subDir;

        if(preg_match('/'.preg_quote($this->modelName, '/').DIRECTORY_SEPARATOR, $dirToRemove)){
            BaseFileHelper::removeDirectory($dirToRemove);

        }

        return true;
    }

    public function getExtension(){
        $ext = pathinfo($this->getPathToOrigin(), PATHINFO_EXTENSION);
        return $ext;
    }

    public function getUrl($size = false){
        $effectsPart = '';
        if(count($this->effects)>0){
            foreach($this->effects as $effect){
                $effectsPart .= $effect->getId();
            }
            $effectsPart = '_under'.$effectsPart;
        }
        $urlSize = ($size) ? '_'.$size : '';
        $url = Url::toRoute([
            '/'.$this->getModule()->id.'/images/image-by-item-and-alias',
            'item' => $this->modelName.$this->itemId,
            'dirtyAlias' =>  $this->urlAlias.$effectsPart.$urlSize.'.'.$this->getExtension()
        ]);

        return $url;
    }

    public function getPath($size = false, $effects = false){
        $urlSize = ($size) ? '_'.$size : '';
        $base = $this->getModule()->getCachePath();
        $sub = $this->getSubDur();

        $origin = $this->getPathToOrigin();

        if($effects){
            $effects = '_'.$effects;
        }
        $filePath = $base.DIRECTORY_SEPARATOR.
            $sub.DIRECTORY_SEPARATOR.$this->urlAlias.$effects.$urlSize.'.'.pathinfo($origin, PATHINFO_EXTENSION);

        if(!file_exists($filePath)){
            $this->createVersion($origin, $size, $effects);

            if(!file_exists($filePath)){
                throw new \Exception('Problem with image creating.');
            }
        }

        return $filePath;
    }

    public function getContent($size = false, $effects = false){
        return file_get_contents($this->getPath($size, $effects));
    }

    public function getPathToOrigin(){

        $base = $this->getModule()->getStorePath();

        $filePath = $base.DIRECTORY_SEPARATOR.$this->filePath;

        return $filePath;
    }


    public function getSizes()
    {
        $sizes = false;
        if($this->getModule()->graphicsLibrary == 'Imagick'){
            $image = new \Imagick($this->getPathToOrigin());
            $sizes = $image->getImageGeometry();
        }else{
            $image = new \abeautifulsite\SimpleImage($this->getPathToOrigin());
            $sizes['width'] = $image->get_width();
            $sizes['height'] = $image->get_height();
        }

        return $sizes;
    }

    public function getSizesWhen($sizeString){

        $size = $this->getModule()->parseSize($sizeString);
        if(!$size){
            throw new \Exception('Bad size..');
        }

        $sizes = $this->getSizes();

        $imageWidth = $sizes['width'];
        $imageHeight = $sizes['height'];
        $newSizes = [];
        if(!$size['width']){
            $newWidth = $imageWidth*($size['height']/$imageHeight);
            $newSizes['width'] = intval($newWidth);
            $newSizes['heigth'] = $size['height'];
        }elseif(!$size['height']){
            $newHeight = intval($imageHeight*($size['width']/$imageWidth));
            $newSizes['width'] = $size['width'];
            $newSizes['heigth'] = $newHeight;
        }

        return $newSizes;
    }

    protected function getSavePath($sizeString = false, $effectsString = false){
        $cachePath = $this->getModule()->getCachePath();
        $subDirPath = $this->getSubDur();
        $fileExtension =  pathinfo($this->filePath, PATHINFO_EXTENSION);
        $effectsPart = '';
        if($effectsString){
            $effectsPart = $effectsString;
        }

        if($sizeString){
            $sizePart = '_'.$sizeString;
        }else{
            $sizePart = '';
        }

        return $cachePath.'/'.$subDirPath.'/'.$this->urlAlias.$effectsPart.$sizePart.'.'.$fileExtension;
    }

    public function createversion($imagePath, $sizeString = false, $effects = false)
    {
        if(strlen($this->urlAlias)<1){
            throw new \Exception('Image without urlAlias!');
        }


        $pathToSave = $this->getSavePath($sizeString, $effects);

        BaseFileHelper::createDirectory(dirname($pathToSave), 0777, true);


        if($sizeString) {
            $size = $this->getModule()->parseSize($sizeString);
        }else{
            $size = false;
        }

            if($this->getModule()->graphicsLibrary == 'Imagick'){
                $image = new \Imagick($imagePath);
                $image->setImageCompressionQuality(100);

                if($size){
                    if($size['height'] && $size['width']){
                        $image->cropThumbnailImage($size['width'], $size['height']);
                    }elseif($size['height']){
                        $image->thumbnailImage(0, $size['height']);
                    }elseif($size['width']){
                        $image->thumbnailImage($size['width'], 0);
                    }else{
                        throw new \Exception('Something wrong with this->module->parseSize($sizeString)');
                    }
                }


                /* ---=== WaterMark ===--- */
                if($this->getModule()->waterMark) {

                    if(!file_exists(Yii::getAlias($this->getModule()->waterMark))){
                        throw new Exception('WaterMark not detected!');
                    }
                    $watermark = new \Imagick();
                    $watermark->readImage(Yii::getAlias($this->getModule()->waterMark));

                    $iWidth = $image->getImageWidth();
                    $iHeight = $image->getImageHeight();
                    $wWidth = $watermark->getImageWidth();
                    $wHeight = $watermark->getImageHeight();



                    if ($iHeight < $wHeight) {
                        // resize the watermark
                        $watermark->scaleImage(false, $iHeight*0.8);
                    }

                    if($iWidth < $wWidth) {
                        // resize the watermark
                        $watermark->scaleImage($iWidth*0.8, false);
                    }
                    $wWidth = $watermark->getImageWidth();
                    $wHeight = $watermark->getImageHeight();

                    $x = ($iWidth - $wWidth) / 2;
                    $y = ($iHeight - $wHeight) / 2;

                    $image->compositeImage($watermark, \Imagick::COMPOSITE_OVER, $x, $y);
                }


                /* --=== Apply effects ===--- */
                if(count($this->getModule()->effects) >0 && $effects){
                    foreach ($this->getModule()->effects as $effect) {
                        $pattern = '/'.preg_quote($effect['id'], '/').'/';
                        if(preg_match($pattern, $effect['id'])){
                            $effect = new $effect['class'];
                            $image = $effect->applyTo($image);
                        }
                    }
                }
                $image->writeImage($pathToSave);
            }else{

                $image = new \abeautifulsite\SimpleImage($imagePath);



                if($size){
                    if($size['height'] && $size['width']){

                        $image->thumbnail($size['width'], $size['height']);
                    }elseif($size['height']){
                        $image->fit_to_height($size['height']);
                    }elseif($size['width']){
                        $image->fit_to_width($size['width']);
                    }else{
                        throw new \Exception('Something wrong with this->module->parseSize($sizeString)');
                    }
                }

                //WaterMark
                if($this->getModule()->waterMark){

                    if(!file_exists(Yii::getAlias($this->getModule()->waterMark))){
                        throw new Exception('WaterMark not detected!');
                    }

                    $wmMaxWidth = intval($image->get_width()*0.4);
                    $wmMaxHeight = intval($image->get_height()*0.4);

                    $waterMarkPath = Yii::getAlias($this->getModule()->waterMark);

                    $waterMark = new \abeautifulsite\SimpleImage($waterMarkPath);



                    if(
                        $waterMark->get_height() > $wmMaxHeight
                        or
                        $waterMark->get_width() > $wmMaxWidth
                    ){

                        $waterMarkPath = $this->getModule()->getCachePath().DIRECTORY_SEPARATOR.
                            pathinfo($this->getModule()->waterMark)['filename'].
                            $wmMaxWidth.'x'.$wmMaxHeight.'.'.
                            pathinfo($this->getModule()->waterMark)['extension'];

                        //throw new Exception($waterMarkPath);
                        if(!file_exists($waterMarkPath)){
                            $waterMark->fit_to_width($wmMaxWidth);
                            $waterMark->save($waterMarkPath, 100);
                            if(!file_exists($waterMarkPath)){
                                throw new Exception('Cant save watermark to '.$waterMarkPath.'!!!');
                            }
                        }

                    }

                    $image->overlay($waterMarkPath, 'bottom right', .5, -10, -10);

                }
            }

        //$image->save($pathToSave, 100);

        return $image;

    }

    public function removeSelf()
    {
        $this->clearCache();

        $storePath = $this->getModule()->getStorePath();

        $fileToRemove = $storePath . DIRECTORY_SEPARATOR . $this->filePath;
        if (preg_match('@\.@', $fileToRemove) and is_file($fileToRemove)) {
            unlink($fileToRemove);
        }
        $this->delete();
    }

    public function setMain($isMain = true){
        if($isMain){
            $this->isMain = 1;
        }else{
            $this->isMain = 0;
        }

    }

    protected function getSubDur(){
        return $this->modelName. 's/' . $this->modelName.$this->itemId;
    }



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'image';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['filePath', 'itemId', 'modelName', 'urlAlias'], 'required'],
            [['itemId', 'isMain'], 'integer'],
            [['filePath', 'urlAlias'], 'string', 'max' => 400],
            [['modelName'], 'string', 'max' => 150]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'filePath' => 'File Path',
            'itemId' => 'Item ID',
            'isMain' => 'Is Main',
            'modelName' => 'Model Name',
            'urlAlias' => 'Url Alias',
        ];
    }
}
