<?php

namespace api\controllers\v1;

use common\models\payment\methods\upay\api\UpayError;
use common\models\payment\methods\upay\api\UpayMethod;
use Exception;
use Yii;
use yii\rest\Controller;

class UpayController extends Controller
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
                return UpayMethod::processApiRequest($data);
            }
            throw new Exception('Error in parsing JSON data', 0);


        } catch (UpayError $e) {
            $message = $e->getMessage();
            return [
                'status'  => $e->getCode(),
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