<?php
/**
 * Created by PhpStorm.
 * User: shavkat
 * Date: 9/8/15
 * Time: 11:32 AM
 */

namespace console\controllers;

use common\models\payment\methods\Paycom;
use common\models\payment\methods\paycom\api\PaycomApiUser;
use common\models\payment\methods\paycom\api\PaycomMethod;
use common\models\payment\Payment;
use common\models\User;
use GuzzleHttp\Client;
use Yii;
use yii\console\Controller;

class PaycomController extends Controller
{
    protected $_localUrl = 'http://api.saxovat.lc/v1/paycom';
    protected $_liveUrl  = 'http://api.saxivat.uz/v1/paycom';

    protected $params = [
        'amount'  => 172400000,
        'account' => [
            'category' => Payment::CATEGORY_GENERAL,
        ],
    ];

    public function actionCheckPerformTransaction()
    {
        $data = [
            'method' => PaycomMethod::METHOD_CHECK_PERFORM_TRANSACTION,
            'params' => array_merge([], $this->params),
            'id'     => rand(1, 10000),

        ];

        $this->request($data);
    }

    public function actionCreateTransaction()
    {
        $data = [
            'method' => PaycomMethod::METHOD_CREATE_TRANSACTION,
            'params' => array_merge([
                                        'id'   => '5305e3bab097f420a62ced0b1',
                                        'time' => PaycomMethod::getCurrentTimeStamp(),
                                    ], $this->params),
            'id'     => rand(1, 10000),

        ];

        $this->request($data);
    }

    public function actionPerformTransaction()
    {
        $data = [
            'method' => PaycomMethod::METHOD_PERFORM_TRANSACTION,
            'params' => [
                'id' => '5305e3bab097f420a62ced0b1',
            ],
            'id'     => rand(1, 10000),

        ];

        $this->request($data);
    }

    public function actionCheckTransaction()
    {
        $data = [
            'method' => PaycomMethod::METHOD_CHECK_TRANSACTION,
            'params' => [
                'id' => '5305e3bab097f420a62ced0b1',
            ],
            'id'     => rand(1, 10000),

        ];

        $this->request($data);
    }

    public function actionCancelTransaction()
    {
        $data = [
            'method' => PaycomMethod::METHOD_CANCEL_TRANSACTION,
            'params' => [
                'id'     => '5305e3bab097f420a62ced0b1',
                'reason' => 5,
            ],
            'id'     => rand(1, 10000),

        ];

        $this->request($data);
    }

    public function actionGetStatement()
    {
        $data = [
            'method' => PaycomMethod::METHOD_GET_STATEMENT,
            'params' => [
                'from' => 0,
                'to'   => PaycomMethod::getCurrentTimeStamp(),
            ],
            'id'     => rand(1, 10000),
        ];

        $this->request($data);
    }

    public function actionSetPassword()
    {
        /**
         * @var $paycom Paycom
         */
        $paycom = Payment::getMethodInstance(Paycom::METHOD_CODE);

        PaycomApiUser::changeApiPassword($paycom->getPassword());
    }

    public function actionChangePassword($password = false)
    {

        $data = [
            'method' => PaycomMethod::METHOD_CHANGE_PASSWORD,
            'params' => [
                'password' => $password ? $password : uniqid(),
            ],
            'id'     => rand(1, 10000),

        ];

        $this->request($data);
    }

    public function actionTime()
    {

        print_r([
                    Yii::$app->formatter->asDatetime(time()),
                    Yii::$app->formatter->asDatetime(round(microtime(true))),
                    Yii::$app->formatter->asDatetime(1452503908132 / 1000),
                    Yii::$app->formatter->timeZone,
                ]);
    }

    protected function request($data)
    {
        $paycom = Payment::getMethodInstance(Paycom::METHOD_CODE);

        $client = new Client([
                                 'base_uri' => YII_DEBUG ? $this->_localUrl : $this->_liveUrl,
                                 'verify'   => false,
                             ]);

        $result = $client->post('', ['body' => json_encode($data), 'auth' => ['Paycom', $paycom->getPassword()]]);
        $json   = $result->getBody()->getContents();

        echo json_encode(@json_decode($json, true), JSON_PRETTY_PRINT) . PHP_EOL;
        /* print_r([
                     'status' => $result->getStatusCode(),
                     'data'   => @json_decode($json, true) ?: $json,
                 ]);*/
    }

    /**
     * @return null|User
     */
    protected function getTestAccount()
    {
        return User::findOne(['mobile' => '909979114']);
    }

    public function actionClean()
    {
        echo Payment::deleteAll(['method' => Paycom::METHOD_CODE, 'live_mode' => ['$ne' => true]]) . PHP_EOL;
        echo Payment::deleteAll([
                                    'method' => Paycom::METHOD_CODE,
                                    'status' => Payment::STATUS_PENDING,
                                    'time'   => ['$lte' => PaycomMethod::getCurrentTimeStamp() - 72 * 3600000],
                                ]) . PHP_EOL;
    }
}