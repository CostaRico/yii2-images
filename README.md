yii2-images
===========
Yii2-images is yii2 module that allow attach images to any your model.
Module requires Imagick library.

You can attach, remove, resize images,
set main image for model

For instance:
  
<pre>
$model = Model::findOne(12); //Model must have id
$model->attachImage('../../image.png');
$model->attachImage('../../image2.png');

//to get all images
$images = $model->getImages();
foreach($images as $img){
    //retun url to full image
    echo $img->getUrl();
    
    //return url to proportionally resized image by width
    echo $img->getUrl('300x');

    //return url to proportionally resized image by height
    echo $img->getUrl('x300');
    
    //return url to resized and cropped image by width and height
    echo $img->getUrl('200x300');
    
    
}
</pre>


Installation:
* install from json
* run migration
* setup module
* setup behaviour 
* check permissions 
