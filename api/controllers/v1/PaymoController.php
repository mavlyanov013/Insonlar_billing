<?php

namespace api\controllers\v1;

use common\models\payment\methods\paymo\api\PaymoError;
use common\models\payment\methods\paymo\api\PaymoMethod;
use Exception;
use Yii;
use yii\rest\Controller;

class PaymoController extends Controller
{
    protected $_postData;

    public function beforeAction($action)
    {
        $this->_postData = Yii::$app->request->getRawBody();

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $id = null;
        try {
            if ($data = @json_decode($this->_postData, true)) {
                return PaymoMethod::processApiRequest($data);
            }
            throw new Exception('Error in parsing JSON data', 0);


        } catch (PaymoError $e) {
            $message = $e->getMessage();
            return [
                'status'  => 0,
                'message' => $message,
            ];
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return [
                'status'  => 0,
                'message' => $message,
                'data'    => [
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ],
            ];
        }
    }
}