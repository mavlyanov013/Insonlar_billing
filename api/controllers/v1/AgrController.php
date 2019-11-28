<?php

namespace api\controllers\v1;


use common\models\payment\methods\agr\AgrApi;
use common\models\payment\methods\agr\AgrRequestException;
use Yii;
use yii\base\Exception;
use yii\rest\Controller;
use yii\web\Response;

class AgrController extends Controller
{
    protected $_postData;

    public function beforeAction($action)
    {
        $this->_postData = @json_decode(Yii::$app->request->getRawBody(), true);
        Yii::$app->response->format = Response::FORMAT_JSON;

        return parent::beforeAction($action);
    }


    public function actionInfo()
    {
        return $this->processRequest(AgrApi::ACTION_INFO);
    }

    public function actionPay()
    {
        return $this->processRequest(AgrApi::ACTION_PAY);
    }

    public function actionNotify()
    {
        return $this->processRequest(AgrApi::ACTION_NOTIFY);
    }

    public function actionCancel()
    {
        return $this->processRequest(AgrApi::ACTION_CANCEL);
    }

    public function actionStatement()
    {
        return $this->processRequest(AgrApi::ACTION_STATEMENT);
    }


    protected function processRequest($action)
    {

        try {
            if (is_array($this->_postData)) {
                return AgrApi::processApiRequest($this->_postData, $action);
            }
            throw new AgrRequestException('Error in request', -8);
        } catch (AgrRequestException $e) {
            $data = [
                'raw'     => Yii::$app->request->getRawBody(),
                'post'    => $this->_postData,
                'message' => $e->getMessage(),
            ];
            Yii::error(print_r($data, true), 'agr');
            return [
                'ERROR'      => $e->getCode(),
                'ERROR_NOTE' => $e->getMessage(),
                //'PARAMETERS' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ];
        } catch (Exception $e) {
            Yii::error(print_r([
                                   'input'   => $this->_postData,
                                   'message' => $e->getMessage(),
                                   'trace'   => $e->getTraceAsString(),
                               ], true), 'agr');

            return [
                'ERROR'      => -7,
                'ERROR_NOTE' => $e->getMessage(),
            ];
        }
    }


}