<?php

namespace api\controllers\v1;

use common\components\Config;
use common\models\payment\methods\paycom\api\PaycomApiUser;
use common\models\payment\methods\paycom\api\PaycomHttpBasicAuth;
use common\models\payment\methods\paycom\api\PaycomJsonRPCError;
use common\models\payment\methods\paycom\api\PaycomMerchantApiException;
use common\models\payment\methods\paycom\api\PaycomMethod;
use Yii;
use yii\rest\Controller;

class PaycomController extends Controller
{
    protected $_postData;

    public function beforeAction($action)
    {
        $this->_postData = Yii::$app->request->getRawBody();

        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        $behaviors                  = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => PaycomHttpBasicAuth::className(),
            'auth'  => function ($username, $password) {
                return PaycomApiUser::getUser($username, $password);
            },
        ];
        return $behaviors;
    }

    public function actionIndex()
    {
        $id = null;
        try {
            if ($data = @json_decode($this->_postData, true)) {
                foreach (['method', 'params', 'id'] as $key) {
                    if (!isset($data[$key])) {
                        throw new PaycomJsonRPCError('Invalid JSON object', -32600);
                    }
                }
                $id = $data['id'];
                return PaycomMethod::processApiRequest($data);
            }
            throw new PaycomJsonRPCError('Error in parsing JSON data', -32700);


        } catch (PaycomMerchantApiException $e) {
            $message = 'PCM. ' . $e->getMessage();
            return [
                'error'  => [
                    'code'    => $e->getCode(),
                    'message' => [
                        'ru' => __($message, [], Config::LANGUAGE_RUSSIAN),
                        'uz' => __($message, [], Config::LANGUAGE_UZBEK),
                        'en' => __($message, [], Config::LANGUAGE_ENGLISH),
                    ],
                    'data'    => [
                        'code'    => $e->getCode(),
                        'message' => $e->getMessage(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                    ],
                ],
                'result' => null,
                'id'     => $id,
            ];
        } catch (\Exception $e) {
            return [
                'error'  => [
                    'code'    => $e->getCode(),
                    'message' => [
                        'ru' => $e->getMessage(),
                        'uz' => $e->getMessage(),
                        'en' => $e->getMessage(),
                    ],
                    'data'    => [
                        'code'    => $e->getCode(),
                        'message' => $e->getMessage(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                    ],
                ],
                'result' => null,
                'id'     => $id,
            ];
        }
    }
}