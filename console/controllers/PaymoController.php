<?php
/**
 * Created by PhpStorm.
 * User: shavkat
 * Date: 9/8/15
 * Time: 11:32 AM
 */

namespace console\controllers;

use common\models\payment\methods\Click;
use common\models\payment\methods\Paymo;
use common\models\payment\Payment;
use common\models\User;
use GuzzleHttp\Client;
use yii\console\Controller;

class PaymoController extends Controller
{
    protected $_localUrl = 'http://api.saxovat.lc/v1/paymo';
    protected $_liveUrl  = 'http://api.mehrli.uz/v1/paymo';

    public function actionTest($amount, $account)
    {
        /**
         * @var $method Click
         */
        $method = Payment::getMethodInstance(Paymo::METHOD_CODE);

        $data = [
            'store_id'         => $method->getStoreId(),
            'amount'           => intval($amount) * 100,
            'account'          => $account,
            'transaction_id'   => rand(100, 1000000),
            'invoice'          => $account,
            'transaction_time' => '2018-12-01 04:01:59',
        ];

        $data['sign'] = md5(
            $data['store_id'] .
            $data['transaction_id'] .
            $data['invoice'] .
            $data['amount'] .
            $method->getPassword()
        );

        print_r($data);

        if ($result = $this->request($data)) {
            print_r($result);
        };
    }

    protected function request($data)
    {
        $client = new Client();
        $result = $client->post(YII_DEBUG ? $this->_liveUrl : $this->_liveUrl,
            ['body' => json_encode($data)]);

        $json = $result->getBody()->getContents();

        return @json_decode($json, true);
    }

    /**
     * @return null|User
     */
    protected function getTestAccount()
    {
        return User::findOne(['mobile' => '+(998) 90 997-91-14']);
    }

    public function actionClean()
    {
        echo Payment::deleteAll(['method' => Click::METHOD_CODE, 'live_mode' => ['$ne' => true]]) . PHP_EOL;
    }
}