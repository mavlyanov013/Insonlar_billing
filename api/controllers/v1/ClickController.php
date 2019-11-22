<?php

namespace api\controllers\v1;


use common\models\payment\methods\click\ApiForm;
use common\models\payment\methods\click\ClickRequestException;
use Yii;
use yii\base\Exception;
use yii\rest\Controller;

class ClickController extends Controller
{
    protected $_postData;

    public function beforeAction($action)
    {
        $this->_postData = Yii::$app->request->post();

        return parent::beforeAction($action);
    }


    public function actionIndex()
    {
        try {
            return ApiForm::processApiRequest($this->_postData);
        } catch (ClickRequestException $e) {
            /*Yii::error($e->getMessage(), 'click');
            Yii::error($e->getTraceAsString(), 'click');*/
            return [
                'error'      => $e->getCode(),
                'error_note' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            Yii::error($e->getMessage(), 'click');
            Yii::error($e->getTraceAsString(), 'click');
            return [
                'error'      => -7,
                'error_note' => $e->getMessage(),
            ];
        }
    }
}