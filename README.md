yii2-images
===========
Yii2-images is yii2 module that allow to attach images to any of your model, next you can get images in any sizes, also you can set main image of images set.

Module requires Imagick library.

Usage instance:
-------------

```php
$model = Model::findOne(12); //Model must have id

//If an image is first it will be main image for this model
$model->attachImage('../../image.png');

//But if you need set another image as main, use second arg
$model->attachImage('../../image2.png', true);

//get all images
$images = $model->getImages();
foreach($images as $img){
    //retun url to full image
    echo $img->getUrl();
    
    //return url to proportionally resized image by width
    echo $img->getUrl('300x');

    //return url to proportionally resized image by height
    echo $img->getUrl('x300');
    
    //return url to resized and cropped (center) image by width and height
    echo $img->getUrl('200x300');
}

//Returns main model image
$image = $model->getImage();

if($image){
    //get path to resized image 
    echo $image->getPath('400x300');
    
    //path to original image
    $image->getPathToOrigin();
    
    //will remove this image and all cache files
    $model->removeImage($image);
}

//get main
```

Installation
-------------
1. Add Yii2-user to the require section of your composer.json file:
```javascript
   {
        "require": {
            "costa-rico/yii2-images": "*"
        }
   }
```
2. run 
<pre>
  php composer.phar update
</pre>

3. run migrate
<pre>
php yii migrate/up --migrationPath=@vendor/costa-rico/yii2-images/migrations
</pre>

4. setup module
<pre>
'modules' => [
        'yii2images' => [
            'class' => 'rico\yii2images\Module',
            //be sure, that permissions ok  
            'imagesStorePath' => 'images/store', //path to origin images
            'imagesCachePath' => 'images/cache', //path to resized copies
        ],
    ],
</pre>

5. attach behaviour to your model
<pre>
    public function behaviors()
    {
        return [
            'image' => [
                'class' => 'rico\yii2images\behaviors\ImageBehave',
            ]
        ];
    }
</pre>

Thats all!

Details
-------------

1. Remove image/images
<pre>
$model->removeImage($image); //you must to pass image (object)

$model->removeImages(); //will remove all images of this model
</pre>

2. Set main image
<pre>
$model->attachImage($absolutePathToImage, true); //will attach image and make it main

foreach($model->getImages() as $img){
    if($img->id == $ourId){
        $model->setMainImage($img);//will set current image main
    }
}
</pre>

3. Get image sizes
<pre>
$image = $model->getImage();
$sizes = $image->getSizesWhen('x500');
echo '&lt;img width="'.$sizes['width'].'" height="'.$sizes['height'].'" src="'.$image->getUrl('x500').'" />';
</pre>

4. Get original image
<pre>
$img = $model->getImage();
echo $img->getPathToOrigin();
</pre>
