<?php

namespace api\controllers\v1;

use api\components\MySoapServer;
use common\models\payment\methods\Kapital;
use common\models\payment\methods\paynet\PaynetMethod;
use Yii;
use yii\rest\Controller;

class KapitalController extends Controller
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

        $server = new \SoapServer("http://api.saxovat.uz/KapitalProviderWebService.wsdl", $options);

        $server->setObject(new PaynetMethod(Kapital::METHOD_CODE, Yii::$app->request->get('version') ? PaynetMethod::VERSION_2 : 0));

        ob_start();
        $server->handle();
        $result = ob_get_contents();
        ob_end_clean();

        Yii::$app->response->content = $result;
    }

}