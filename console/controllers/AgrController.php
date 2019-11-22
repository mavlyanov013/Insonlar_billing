<?php
/**
 * Created by PhpStorm.
 * User: shavkat
 * Date: 9/8/15
 * Time: 11:32 AM
 */

namespace console\controllers;

use common\models\payment\methods\Agr;
use common\models\payment\methods\agr\AgrApi;
use common\models\payment\Payment;
use common\models\User;
use GuzzleHttp\Client;
use yii\console\Controller;

class AgrController extends Controller
{
    protected $_localUrl = 'http://api.qoqon.lc/v1/agr/';
    protected $_liveUrl  = 'https://insonlar.mehrli.uz/v1/agr/';

    public function actionInfo($transId, $amount)
    {
        /**
         * @var $method Agr
         */
        $method = Payment::getMethodInstance(Agr::METHOD_CODE);

        $data = [
            'MERCHANT_TRANS_ID' => $transId,
            'SIGN_TIME'         => Payment::getCurrentTimeStamp(),
        ];

        $data['SIGN_STRING'] = md5(
            $method->getSecretKey() .
            $data['MERCHANT_TRANS_ID'] .
            $data['SIGN_TIME']
        );

        $result = $this->request($data, AgrApi::SCENARIO_INFO);

        $agrTransId = rand(1, 100000);

        if ($result && $result['ERROR'] == 0) {
            $data = [
                'VENDOR_ID'             => $method->getVendorId(),
                'PAYMENT_ID'            => 1,
                'PAYMENT_NAME'          => 'test',
                'AGR_TRANS_ID'          => $agrTransId,
                'MERCHANT_TRANS_ID'     => $transId,
                'MERCHANT_TRANS_AMOUNT' => intval($amount),
                'ENVIRONMENT'           => 'live',
                'MERCHANT_TRANS_DATA'   => '',
                'SIGN_TIME'             => Payment::getCurrentTimeStamp(),
            ];

            $data['SIGN_STRING'] = md5(
                $method->getSecretKey() .
                $data['AGR_TRANS_ID'] .
                $data['VENDOR_ID'] .
                $data['PAYMENT_ID'] .
                $data['PAYMENT_NAME'] .
                $data['MERCHANT_TRANS_ID'] .
                $data['MERCHANT_TRANS_AMOUNT'] .
                $data['ENVIRONMENT'] .
                $data['SIGN_TIME']
            );

            $result = $this->request($data, AgrApi::SCENARIO_PAY);

            if ($result && $result['ERROR'] == 0) {

                $data = [
                    'AGR_TRANS_ID'    => $agrTransId,
                    'VENDOR_TRANS_ID' => $result['VENDOR_TRANS_ID'],
                    'STATUS'          => AgrApi::STATUS_PAYED,
                    'SIGN_TIME'       => Payment::getCurrentTimeStamp(),
                ];

                $data['SIGN_STRING'] = md5(
                    $method->getSecretKey() .
                    $data['AGR_TRANS_ID'] .
                    $data['VENDOR_TRANS_ID'] .
                    $data['STATUS'] .
                    $data['SIGN_TIME']
                );

                $result = $this->request($data, AgrApi::SCENARIO_NOTIFY);
            }
        }
    }

    public function actionCancel($transId)
    {
        $transId = intval($transId);
        $method  = Payment::getMethodInstance(Agr::METHOD_CODE);

        $data = [
            'AGR_TRANS_ID'    => $transId,
            'VENDOR_TRANS_ID' => $transId,
            'SIGN_TIME'       => Payment::getCurrentTimeStamp(),
        ];

        $data['SIGN_STRING'] = md5(
            $method->getSecretKey() .
            $data['AGR_TRANS_ID'] .
            $data['VENDOR_TRANS_ID'] .
            $data['SIGN_TIME']
        );

        $result = $this->request($data, AgrApi::SCENARIO_CANCEL);

        if ($result && $result['ERROR'] == 0) {
            $data = [
                'AGR_TRANS_ID'    => $transId,
                'VENDOR_TRANS_ID' => $transId,
                'STATUS'          => AgrApi::STATUS_CANCELLED,
                'SIGN_TIME'       => Payment::getCurrentTimeStamp(),
            ];

            $data['SIGN_STRING'] = md5(
                $method->getSecretKey() .
                $data['AGR_TRANS_ID'] .
                $data['VENDOR_TRANS_ID'] .
                $data['STATUS'] .
                $data['SIGN_TIME']
            );

            $result = $this->request($data, AgrApi::SCENARIO_NOTIFY);
        }
    }

    protected function request($data, $action)
    {
        $client = new Client(['verify'=>false]);
        $result = $client->post((YII_DEBUG ? $this->_localUrl : $this->_liveUrl) . $action,
            ['body' => json_encode($data)]);

        $json = $result->getBody()->getContents();

        echo json_encode(@json_decode($json, true), JSON_PRETTY_PRINT) . PHP_EOL;

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
        echo Payment::deleteAll(['method' => Agr::METHOD_CODE, 'live_mode' => ['$ne' => true]]) . PHP_EOL;
    }
}