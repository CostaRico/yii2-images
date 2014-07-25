<?php

use yii\codeception\TestCase;
use Codeception\Util\Debug;

class ImageModuleTest extends  TestCase
{
    use \Codeception\Specify;



    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
    }


    public function testGetAliasString(){

    }

    public function testParseImageAlias()
    {
        $this->specify("Bad size string", function() {
            $data = Yii::$app->getModule('yii2images')->parseImageAlias('test_sdf_');
            $this->assertEquals($data['alias'], null);
            $this->assertEquals($data['size'], null);

        });

        $this->specify("\nNo '_' at end without size!!!", function() {
            try{
                $data = Yii::$app->getModule('yii2images')->parseImageAlias('asdfds_');

            }catch (\Exception $e){
                $this->assertEquals("Something bad with size, sorry!",$e->getMessage());
            }


        });

        $data = Yii::$app->getModule('yii2images')->parseImageAlias('asdfa_234');
        $this->assertEquals('asdfa', $data['alias']);
        $this->assertEquals(null, $data['size']['height']);
        $this->assertEquals('234', $data['size']['width']);

        $data = Yii::$app->getModule('yii2images')->parseImageAlias('asdfa_234x222');
        $this->assertEquals('asdfa', $data['alias']);
        $this->assertEquals('222', $data['size']['height']);
        $this->assertEquals('234', $data['size']['width']);


    }

    public function provider()
    {
        return ['one', 'two', 'three'];
    }
}