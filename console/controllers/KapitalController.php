<?php
/**
 * Created by PhpStorm.
 * User: shavkat
 * Date: 9/8/15
 * Time: 11:32 AM
 */

namespace console\controllers;

use common\models\payment\methods\Kapital;
use common\models\payment\methods\Paycom;
use common\models\payment\methods\paycom\api\PaycomApiUser;
use common\models\payment\methods\paycom\api\PaycomMethod;
use common\models\payment\methods\Paynet;
use common\models\payment\Payment;
use common\models\User;
use GuzzleHttp\Client;
use SoapClient;
use Yii;
use yii\console\Controller;

class KapitalController extends Controller
{
    protected $params = [
        'username'  => 'kapital',
        'password'  => 'kapital@q0q0np@r0l$3cure',
        'serviceId' => 1,
    ];

    /**
     * @var SoapClient
     */
    private $client;

    public function init()
    {
        ini_set("soap.wsdl_cache_enabled", "0");
        ini_set('soap.wsdl_cache_ttl', '0');
        $options      = array(
            "trace"              => true,
            "exceptions"         => true,
            "connection_timeout" => 30,
            'cache_wsdl'         => 0,
            'encoding'           => 'UTF-8',
            'soap_version'       => SOAP_1_2,
        );
        $this->client = new SoapClient('http://api.saxovat.uz/KapitalProviderWebService.wsdl', $options);

        parent::init();
    }

    public function actionGetInformation()
    {
        $this->request('GetInformation', []);
    }

    public function actionPerformTransaction($transactionId)
    {
        $this->request('PerformTransaction',
                       [
                           'amount'          => 1000000,
                           'transactionId'   => $transactionId,
                           'transactionTime' => $this->getFormattedDate(),
                       ]
        );
    }

    public function actionCheckTransaction($transactionId)
    {
        $this->request('CheckTransaction',
                       [
                           'transactionId'   => $transactionId,
                           'transactionTime' => $this->getFormattedDate(),
                       ]
        );
    }

    public function actionCancelTransaction($transactionId)
    {
        $this->request('CancelTransaction',
                       [
                           'transactionId'   => $transactionId,
                           'transactionTime' => $this->getFormattedDate(),
                       ]
        );
    }

    public function actionGetStatement()
    {
        $this->request('GetStatement',
                       [
                           'dateFrom' => $this->getFormattedDate(time() - 24 * 3600),
                           'dateTo'   => $this->getFormattedDate(time() + 24 * 3600),
                       ]
        );
    }

    public function actionChangePassword($newPassword)
    {
        $this->request('ChangePassword',
                       [
                           'newPassword' => $newPassword,
                       ]
        );
    }

    public function actionTest()
    {
        echo $this->getFormattedDate();
    }

    protected function getFormattedDate($time = false)
    {
        $format = "Y-m-d\TH:i:s.uP";
        $date   = new \DateTime('now');
        if ($time) $date->setTimestamp($time);
        $d = $date->format($format);

        return str_replace('+', '1+', $d);
    }

    protected function request($method, $params = [])
    {
        $params = array_merge($this->params, $params);
        $result = $this->client->$method($params);


        print_r((array)$result);
    }



    public function actionUser($name = false, $password = false)
    {
        /**
         * @var Kapital $paynet
         */

        if (!$name) $name = $this->params['username'];
        if (!$password) $password = $this->params['password'];

        $paynet = Payment::getMethodInstance(Kapital::METHOD_CODE);

        if ($paynet->setUser($name, $password)) {
            echo "OK\n";
        }
    }

    public function actionClean()
    {
        echo Payment::deleteAll(['method' => Kapital::METHOD_CODE, 'live_mode' => ['$ne' => true]]) . PHP_EOL;
    }


}