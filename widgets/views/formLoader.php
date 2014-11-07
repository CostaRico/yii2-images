<?
/**
 * @var $this yii\web\View
 * @var $urls []
 */
use rico\yii2images\Asset;
Asset::register($this);

$this->registerJs(
    '
   // var ricoRemoveImagesUrl = '.$urls['addImage'].';
    var ricoRemoveImagesUrl = \''.$urls['removeImage'].'\';
    var ricoSetMainUrl = \''.$urls['setMain'].'\';
    ', $this::POS_BEGIN
);

$images = $model->getImages();
?>
<div class="container ricoImages">
    <?if(count($images)>0 and get_class($images[0])!='rico\yii2images\models\PlaceHolder'){?>
        <div class="row">
            <?
            $c = 0;
            foreach($images as $img){
                $c++;?>
                <div class="col-xs-3">
                    <a href="#" style="" class="glyphicon glyphicon-remove"></a>
                    <a href="#" id="<?=$img->id?>" class="thumbnail <?=($c==1) ? 'selectedImg' : ''?>">
                        <img src="<?=$img->getUrl('260x200')?>">
                    </a>
                </div>
            <?}?>

        </div>
    <?}?>

</div>
<input type="hidden" class="modelId" value="<?=$model->id?>" />
<?= $form->field($model, 'file[]')->fileInput(['multiple' => '']) ?>