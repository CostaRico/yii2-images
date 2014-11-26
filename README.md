yii2-images
===========
Yii2-images is yii2 module that allows to attach images to any of your models, next you can get images in any sizes, also you can set main image of images set.

Module supports Imagick and GD libraries, you can set up it in module settings.


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

```

Details
-------------
1. Get images
    ```php
    $model->getImage(); //returns main image for model (first added image or setted as main)
    
    $model->removeImages(); //returns array with images
    
    //If there is no images for model, above methods will return PlaceHolder images or null
    //If you want placeholder set up it in module configuration (see documentation)
    
    ```
2. Remove image/images
    ```php
    $model->removeImage($image); //you must to pass image (object)
    
    $model->removeImages(); //will remove all images of this model
    ```

3. Set main image
    ```php
    $model->attachImage($absolutePathToImage, true); //will attach image and make it main
    
    foreach($model->getImages() as $img){
        if($img->id == $ourId){
            $model->setMainImage($img);//will set current image main
        }
    }
    ```

4. Get image sizes
    ```php
    $image = $model->getImage();
    $sizes = $image->getSizesWhen('x500');
    echo '&lt;img width="'.$sizes['width'].'" height="'.$sizes['height'].'" src="'.$image->getUrl('x500').'" />';
    ```

5. Get original image
    ```php
    $img = $model->getImage();
    echo $img->getPathToOrigin();
    ```


Installation
-------------
1. Add Yii2-user to the require section of your composer.json file:
    <pre>
       {
            "require": {
                "costa-rico/yii2-images": "dev-master"
            }
       }
    </pre>
2. run 
    <pre>
      php composer.phar update
    </pre>

3. run migrate
    <pre>
    php yii migrate/up --migrationPath=@vendor/costa-rico/yii2-images/migrations
    </pre>

4. setup module
    ```php
    'modules' => [
            'yii2images' => [
                'class' => 'rico\yii2images\Module',
                //be sure, that permissions ok 
                //if you cant avoid permission errors you have to create "images" folder in web root manually and set 777 permissions
                'imagesStorePath' => 'images/store', //path to origin images
                'imagesCachePath' => 'images/cache', //path to resized copies
                'graphicsLibrary' => 'GD', //but really its better to use 'Imagick' 
                'placeHolderPath' => '@webroot/images/placeHolder.png', // if you want to get placeholder when image not exists, string will be processed by Yii::getAlias
            ],
        ],
    ```

5. attach behaviour to your model (be sure that your model has "id" property)
    ```php
        public function behaviors()
        {
            return [
                'image' => [
                    'class' => 'rico\yii2images\behaviors\ImageBehave',
                ]
            ];
        }
    ```

Thats all!


