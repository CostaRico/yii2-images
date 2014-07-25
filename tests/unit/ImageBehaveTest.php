<?php
namespace models;

use Yii;
use yii\codeception\DbTestCase;
use yii\codeception\TestCase;
use Codeception\Util\Stub;
use rico\yii2images\behaviors\ImageBehave;
use rico\yii2images\models\Image;
use yii\db\Connection;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use org\bovigo\vfs\vfsStream;
use rico\yii2images\controllers\ImagesController;

use Codeception\Util\Debug;

class ImageBehaveTest extends DbTestCase
{

    use \Codeception\Specify;

    /**
     * @var \CodeGuy
     */
    protected $codeGuy;

    protected $dbConnection;

    private $model;


    public static function setUpBeforeClass()
    {
        if (!extension_loaded('pdo') || !extension_loaded('pdo_sqlite')) {
            static::markTestSkipped('PDO and SQLite extensions are required.');
        }
    }


    public function setUp()
    {
        parent::setUp();


        $config = ArrayHelper::merge(require(Yii::getAlias($this->appConfig)), [
            'components' => [
                'db' => [
                    'class' => '\yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ]
            ],
            'modules' => [
                'yii2images' => [
                    'class' => 'rico\yii2images\Module',

                ],
            ],
        ]);

        $this->mockApplication($config);

        $columns = [
            'id' => 'pk',
            'name' => 'string',
        ];
        Yii::$app->getDb()->createCommand()->createTable('test_image_behavior', $columns)->execute();

        $columns = [
            'id' => 'pk',
            'filePath' => 'VARCHAR(400) NOT NULL',
            'itemId' => 'int(20) NOT NULL',
            'isMain' => 'int(1)',
            'modelName' => 'VARCHAR(150) NOT NULL',
            'urlAlias' => 'VARCHAR(400) NOT NULL',
        ];
        Yii::$app->getDb()->createCommand()->createTable('image', $columns)->execute();

        vfsStream::setup('root');
        vfsStream::setup('root/Cache');
        $module = Yii::$app->getModule('yii2images');
        $module->imagesStorePath = vfsStream::url('root/Store');
        $module->imagesCachePath = vfsStream::url('root/Cache');

        $this->model = new ActiveRecordImage();
        $this->model->name = 'testName';
        $this->model->save();

    }

    public function tearDown()
    {
        Yii::$app->getDb()->close();

        parent::tearDown();
    }


    protected function _before()
    {
    }

    protected function _after()
    {
    }


    public function testAttachImage()
    {
        $this->model->attachImage(__DIR__ . '/data/testPicture.jpg');


        //Check if file dir exists
        $this->assertTrue(
            file_exists(vfsStream::url('root/Store/ActiveRecordImages/ActiveRecordImage' . $this->model->id . '/'))
            &&
            is_dir(vfsStream::url('root/Store/ActiveRecordImages/ActiveRecordImage' . $this->model->id . '/'))
        );

        //Check file exists
        $file = scandir(vfsStream::url('root/Store/ActiveRecordImages/ActiveRecordImage' . $this->model->id . '/'))[2];
        $this->assertTrue(isset($file));

        //Check file extension
        $ext = substr($file, strlen($file) - 3);
        $this->assertTrue($ext == 'jpg');

        //Check db record
        $imageRecord = Image::find()->where([
            'itemId' => $this->model->id,
            'modelName' => 'ActiveRecordImage'
        ])->one();

        //var_dump($imageRecord);die;
        $this->assertTrue($imageRecord->isMain == 1);
        $this->assertTrue($imageRecord->filePath == 'ActiveRecordImages/ActiveRecordImage1/' . $file);

    }

    /**
     * @depends testAttachImage
     */
    public function testGetImages()
    {
        //Check one image
        $this->model->attachImage(__DIR__ . '/data/testPicture.jpg');
        $images = $this->model->getImages();
        $this->assertTrue(count($images) == 1);

        //Check several images
        $this->model->attachImage(__DIR__ . '/data/testPicture.jpg');
        $this->model->attachImage(__DIR__ . '/data/testPicture.jpg');
        $this->model->attachImage(__DIR__ . '/data/testPicture.jpg');

        $images = $this->model->getImages();
        $this->assertTrue(count($images) == 4);


        //Check is it first image main
        $this->assertTrue($images[0]->isMain == 1);

    }

    /**
     * @depends testAttachImage
     */
    public function testGetImage()
    {
        //Check one image
        $this->model->attachImage(__DIR__ . '/data/testPicture.jpg');
        $image = $this->model->getImage();
        $this->assertTrue(get_class($image) == 'rico\yii2images\models\Image');

        //Check several images
        $this->model->attachImage(__DIR__ . '/data/testPicture.jpg');
        $this->model->attachImage(__DIR__ . '/data/testPicture.jpg');
        $this->model->attachImage(__DIR__ . '/data/testPicture.jpg');

        $image = $this->model->getImage();
        $this->assertTrue(get_class($image) == 'rico\yii2images\models\Image');

        //Check is it first image main
        $this->assertTrue($image->isMain == 1);

    }


    /**
     * @depends testAttachImage
     * @depends testGetImage
     */
    public function testRemoveImage()
    {

        $this->model->attachImage(__DIR__ . '/data/testPicture.jpg');
        $img = $this->model->getImage();

        //Make cache copy
        $file = scandir(vfsStream::url('root/Store/ActiveRecordImages/ActiveRecordImage1'))[2];


        mkdir(vfsStream::url('root/Cache/ActiveRecordImages/'));
        mkdir(vfsStream::url('root/Cache/ActiveRecordImages/ActiveRecordImage1'));


        copy(vfsStream::url('root/Store/ActiveRecordImages/ActiveRecordImage1') . '/' . $file,

            vfsStream::url('root/Cache/ActiveRecordImages/ActiveRecordImage1') . '/' . $file
        );

        $this->assertTrue(file_exists(vfsStream::url('root/Cache/ActiveRecordImages/ActiveRecordImage1') . '/' . $file));


        $this->model->removeImage($img);

        //Check db record removed
        $imageRecord = Image::find()->where([
            'itemId' => $this->model->id,
            'modelName' => 'ActiveRecordImage'
        ])->one();

        $this->assertTrue($imageRecord == NULL);

        //check files not exists
        $files = scandir(vfsStream::url('root/Store/ActiveRecordImages/ActiveRecordImage1'));
        $this->assertTrue(count($files) == 2);

        //Check cache file and folder
        $this->assertFalse(file_exists(vfsStream::url('root/Cache/ActiveRecordImages/ActiveRecordImage1') . '/' . $file));
        $this->assertFalse(file_exists(vfsStream::url('root/Cache/ActiveRecordImages/ActiveRecordImage1')));

    }

    /**
     * @depends testAttachImage
     * @depends testGetImage
     */
    public function testSetMainImage()
    {
        $this->model->attachImage(__DIR__ . '/data/testPicture.jpg');
        $img = $this->model->getImage();

        $this->assertTrue($img->isMain == 1);

        //Remember main image id
        $oldMainImageId = $img->id;

        $this->model->attachImage(__DIR__ . '/data/testPicture.jpg');
        $newMainImage = $this->model->attachImage(__DIR__ . '/data/testPicture.jpg', true);
        $this->model->attachImage(__DIR__ . '/data/testPicture.jpg');


        $images = $this->model->getImages();
        foreach ($images as $i) {
            if ($i->isMain == 0) {
                $this->assertTrue($i->id != $newMainImage->id);
            } else {
                $this->assertTrue($i->id == $newMainImage->id);
            }
        }

        $this->assertTrue($oldMainImageId != $newMainImage->id);
    }


    public function testSeveralModels()
    {
        $anotherModel = new ActiveRecordImage2();
        $anotherModel->name = 'testName';
        $anotherModel->save();

        $img = $this->model->attachImage(__DIR__ . '/data/testPicture.jpg');
        $this->model->attachImage(__DIR__ . '/data/testPicture.jpg');
        $anotherImage =  $anotherModel->attachImage(__DIR__ . '/data/testPicture.jpg');

        $this->assertTrue($anotherImage->modelName == 'ActiveRecordImage2');
        $this->assertTrue($img->modelName == 'ActiveRecordImage');

        $this->assertTrue(count($anotherModel->getImages())==1);
        $this->assertTrue(count($this->model->getImages())>1);
    }

}

class ActiveRecordImage extends ActiveRecord
{
    public function behaviors()
    {
        return [
            ImageBehave::className(),
        ];
    }

    public static function tableName()
    {
        return 'test_image_behavior';
    }

    public function getStorePath()
    {
        return vfsStream::setup('testImagesDir');
    }
}

class ActiveRecordImage2 extends ActiveRecord
{
    public function behaviors()
    {
        return [
            ImageBehave::className(),
        ];
    }

    public static function tableName()
    {
        return 'test_image_behavior';
    }

    public function getStorePath()
    {
        return vfsStream::setup('testImagesDir');
    }
}