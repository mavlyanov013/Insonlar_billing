<?php

namespace api\controllers\v1;

use common\components\Config;
use common\models\payment\methods\Apelsin;
use common\models\payment\methods\paycom\api\PaycomApiUser;
use common\models\payment\methods\paycom\api\PaycomHttpBasicAuth;
use common\models\payment\methods\paycom\api\PaycomJsonRPCError;
use common\models\payment\methods\paycom\api\PaycomMerchantApiException;
use common\models\payment\methods\paycom\api\PaycomMethod;
use common\models\payment\methods\Paynet;
use common\models\payment\methods\paynet\PaynetMethod;
use common\models\payment\Payment;
use SoapFault;
use SoapServer;
use Yii;
use yii\rest\Controller;
use yii\web\Response;

class ApelsinController extends Controller
{
    protected $_postData;

    public function init()
    {
        ini_set("soap.wsdl_cache_enabled", "0");
    }

    public function actionIndex()
    {

        $options = [
            'cache_wsdl'   => 1,
            'encoding'     => 'UTF-8',
            'soap_version' => SOAP_1_2,
        ];

        $server = new \SoapServer("https://mexrli.complex-solutions.uz/ApelsinProviderWebService.wsdl", $options);

        $server->setObject(new PaynetMethod(Apelsin::METHOD_CODE));

        ob_start();
        $server->handle();
        $result = ob_get_contents();
        ob_end_clean();

        Yii::$app->response->content = $result;
    }

}