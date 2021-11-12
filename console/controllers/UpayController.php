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
use common\models\payment\methods\Upay;
use common\models\payment\Payment;
use common\models\User;
use GuzzleHttp\Client;
use yii\console\Controller;

class UpayController extends Controller
{
    protected $_localUrl = 'http://api.saxovat.lc/v1/upay';
    protected $_liveUrl  = 'http://insonlar.mehrli.uz/v1/upay';

    public function actionTest($amount, $account, $live = false)
    {
        /**
         * @var $method Upay
         */
        $method = Payment::getMethodInstance(Upay::METHOD_CODE);

        $data = [
            'upayPaymentAmount' => intval($amount),
            'personalAccount'   => $account,
            'upayTransId'       => rand(100, 1000000),
            'upayTransTime'     => '2018-12-01 04:01:59',
        ];

        $data['accessToken'] = md5(
            $data['upayTransId'] .
            $data['upayPaymentAmount'] .
            $data['upayTransTime'] .
            $method->getServiceId()
        );

        if ($result = $this->request($data, $live)) {
            print_r($result);
        };
    }

    protected function request($data, $live = false)
    {
        $client = new Client();
        $result = $client->post($live ? $this->_liveUrl : $this->_localUrl,
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
        echo Payment::deleteAll(['method' => Upay::METHOD_CODE, 'live_mode' => ['$ne' => true]]) . PHP_EOL;
    }
}