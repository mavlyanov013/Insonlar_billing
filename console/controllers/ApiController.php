<?php
/**
 * Created by PhpStorm.
 * User: shavkat
 * Date: 9/8/15
 * Time: 11:32 AM
 */

namespace console\controllers;

use common\models\_MongoModel;
use common\models\Admin;
use common\models\license\Coupon;
use common\models\license\Product;
use GuzzleHttp\Client;
use Yii;
use yii\console\Controller;
use yii\mongodb\ActiveRecord;

class ApiController extends Controller
{
    protected $_localUrl = 'http://api.lic.lc/v1/';
    protected $_liveUrl  = 'https://api.apps.activemedia.uz/v1/';

    protected $packageId = 'uz.activemedia.paycomtest';
    protected $deviceId  = '30ea750b682bd87d5fcb8d63dfa143b7';
    protected $lang      = 'uz-UZ';

    protected $deviceData = [
        'vendor' => 'Samsung',
        'model'  => 'Galaxy One',
        'api'    => 14,
    ];

    protected $cardDataLive = [
        'number'    => '860014******1148',
        'expire'    => '04/19',
        'token'     => '59ccd82a52725c034a26a4a5_Z3CFOFJkicFZmpg0AyoGqeTvqhoSnFakRi4iC5M',
        'recurrent' => false,
        'verify'    => true,
    ];

    protected $cardDataTest = [
        'number'    => '444444******4444',
        'expire'    => '04/20',
        'token'     => 'NTljZTFlZmQ0ZTU2ODJlNDRhOWYwZWU3X0olV3NWQjQxKyF5aWlzJHdLQDhFZl9INCg1Z0AyUGI3QVQyVDdnYVlCMEUhaD0tJkZ0TihnReKElj9yRnUtakZ0SiM3NDM/ellXWjdnZ0lfJS0jX2gwQkIxczFEaHdwa3BtWmhKeWVQTmdKJElvYzZBdUpoR0lwR3RWWm1mRERkeTE5dkorWSYoYVg5OHQ2Zi1fN0J2SVZ4OVhuM0RhdDZ6Qi00ckA4ISR6R3dEJjg0LUIpanh6WFV2alVhUjjihJZTPyYkbl9GWVglZ0hhZEVrYThCWUFuIVZxbUM/NDRiQXo4blA2RWtnaklvXkktZ3R3V21xd3l5QE1qP3J3Jm40RVU2',
        'recurrent' => false,
        'verify'    => true,
    ];

    public function actionProductList()
    {
        $this->request('license/products', [], ['productId' => 'APP']);
    }


    public function actionLicenseStatus()
    {
        $this->request('license/status', [], ['productId' => 'APP']);
    }

    public function actionLicenseAddPaycom()
    {
        $this->request(
            'license/add-paycom',
            [
                'deviceData'  => $this->deviceData,
                'paymentData' => YII_DEBUG ? $this->cardDataTest : $this->cardDataLive,
            ]
        );
    }


    protected function request($url, $data)
    {
        $client = new Client([
                                 'base_uri' => YII_DEBUG ? $this->_localUrl : $this->_liveUrl,
                                 'verify'   => false,
                             ]);

        $params = [
            'deviceId'  => $this->deviceId,
            'packageId' => $this->packageId,
            'productId' => 'APP',
            'l'         => $this->lang,
        ];

        $url    = $url . "?" . \GuzzleHttp\Psr7\build_query($params);
        $result = $client->post($url, ['form_params' => $data]);

        $json = $result->getBody()->getContents();

        echo PHP_EOL . (YII_DEBUG ? $this->_localUrl : $this->_liveUrl) . $url . PHP_EOL . PHP_EOL;
        echo json_encode(@json_decode($json, true), JSON_PRETTY_PRINT) . PHP_EOL;


        return @json_decode($json, true);
    }

    public function actionAdd()
    {
        $file = Yii::getAlias('@runtime/paycom.data');

        $this->request(
            'license/add-paycom',
            [
                'deviceData'  => json_encode($this->deviceData),
                'paymentData' => file_get_contents($file),
            ]
        );
    }

    public function actionCode()
    {
        $product = Product::find()->where(['productId' => 'APP'])->one();

        Coupon::generateForCode($product, Coupon::offerCode('TEST'));
        Coupon::generateForProduct($product, 1, 'HAPP-YWOR-LD');
        Coupon::generateForProduct($product, 1, 'HAPP-Y');
        Coupon::generateForProduct($product, 1, 'HAPP-');
        Coupon::generateForProduct($product, 1, 'HAPP');
        Coupon::generateForProduct($product, 1, '');
    }

}