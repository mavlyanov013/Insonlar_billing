<?php
/**
 * Created by PhpStorm.
 * User: shavkat
 * Date: 9/8/15
 * Time: 11:32 AM
 */

namespace console\controllers;

use common\models\payment\methods\Click;
use common\models\payment\methods\click\ApiForm;
use common\models\payment\Payment;
use common\models\User;
use GuzzleHttp\Client;
use Yii;
use yii\console\Controller;

class ClickController extends Controller
{
    protected $_localUrl = 'http://api.saxovat.lc/v1/click';
    protected $_liveUrl  = 'https://api.mehrli.uz/v1/click';

    public function actionTest()
    {
        /**
         * @var $method Click
         */
        $method = Payment::getMethodInstance(Click::METHOD_CODE);

        $data = [
            'action'            => ApiForm::ACTION_PREPARE,
            'amount'            => 5000,
            'click_trans_id'    => 12321311,
            'service_id'        => $method->getMerchantServiceId(),
            'click_paydoc_id'   => 100101,
            'merchant_trans_id' => "Шавкат",
            'sign_time'         => '2018-12-01 04:01:59',
        ];

        $data['sign_string'] = md5(
            $data['click_trans_id'] .
            $data['service_id'] .
            $method->getSecretKey() .
            $data['merchant_trans_id'] .
            $data['amount'] .
            $data['action'] .
            $data['sign_time']
        );

        if ($result = $this->request($data)) {

            $data['action']              = ApiForm::ACTION_COMPLETE;
            $data['merchant_prepare_id'] = $result['merchant_prepare_id'];

            $data['sign_string'] = md5(
                $data['click_trans_id'] .
                $data['service_id'] .
                $method->getSecretKey() .
                $data['merchant_trans_id'] .
                $data['merchant_prepare_id'] .
                $data['amount'] .
                $data['action'] .
                $data['sign_time']
            );

            $this->request($data);
        };
    }

    protected function request($data)
    {
        $client = new Client();
        $result = $client->post(YII_DEBUG ? $this->_localUrl : $this->_liveUrl,
                                ['form_params' => $data]);

        $json = $result->getBody()->getContents();

        echo json_encode(@json_decode($json, true), JSON_PRETTY_PRINT) . PHP_EOL;
        print_r([
                    'status' => $result->getStatusCode(),
                    'data'   => @json_decode($json, true) ?: $json,
                ]);

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