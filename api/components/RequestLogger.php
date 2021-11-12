<?php
/**
 * Created by PhpStorm.
 * User: complex
 * Date: 6/22/15
 * Time: 10:50 AM
 */

namespace api\components;


use Yii;
use yii\base\Event;
use yii\web\Response;

class RequestLogger
{
    public static function logRequest(Event $event)
    {

        /**
         * @var $response Response
         */
        $response   = $event->sender;
        $controller = Yii::$app->controller ? Yii::$app->controller->id : '';

        if ($controller == 'v1/paycom') {
            if ($response->getStatusCode() == 401) {
                $response->setStatusCode(200);
                $requestData    = @json_decode(Yii::$app->request->getRawBody(), true);
                $response->data = [
                    'error'  => [
                        'code'    => -32504,
                        'message' => [
                            'uz' => 'Avtorizatsiyalanmagan murojaat',
                            'ru' => 'Не авторизованный доступ',
                            'en' => 'Unauthorized access',
                        ],
                    ],
                    'result' => null,
                    'id'     => $requestData && isset($requestData['id']) ? $requestData['id'] : null,
                ];
            }

            $data = [
                'jsn' => Yii::$app->request->getRawBody(),
                'inp' => json_decode(Yii::$app->request->getRawBody(), true),
                'out' => $response->data,
            ];

            if (isset($response->data['error'])) {
                Yii::error(print_r($data, true), 'paycom');
                if (isset($response->data['error']['data']) && !YII_DEBUG) {
                    unset($response->data['error']['data']);
                }
            } else {
                Yii::warning(print_r($data, true), 'paycom');
            }
        } else if ($controller == 'v1/paynet') {
            Yii::$app->response->format = Response::FORMAT_XML;

            $data = [
                'inp' => Yii::$app->request->getRawBody(),
                'out' => $response->content,
            ];

            Yii::trace(print_r($data, true), 'paynet');

            if ($response->statusCode != 200) {
                Yii::error(print_r($data, true), 'paynet');
            }
        } else if ($controller == 'v1/kapital') {
            Yii::$app->response->format = Response::FORMAT_XML;

            $data = [
                'inp' => Yii::$app->request->getRawBody(),
                'out' => $response->content,
            ];

            Yii::trace(print_r($data, true), 'kapital');

            if ($response->statusCode != 200) {
                Yii::error(print_r($data, true), 'kapital');
            }
        } elseif ($controller == 'v1/click') {

            Yii::$app->response->format = Response::FORMAT_JSON;
            $data                       = [
                'inp' => Yii::$app->request->getRawBody(),
                'out' => $response->data,
            ];
            Yii::trace(print_r($data, true), 'click');
        } elseif ($controller == 'v1/paymo') {

            Yii::$app->response->format = Response::FORMAT_JSON;
            $data                       = [
                'inp' => Yii::$app->request->getRawBody(),
                'out' => is_array($response->data) ? json_encode($response->data) : $response->data,
            ];
            Yii::trace(print_r($data, true), 'paymo');

            if ($response->statusCode != 200) {
                $response->data = ['status' => 0, 'message' => 'Server error', 'data' => $response->data];
            } else {
                Yii::error(print_r($data, true), 'paymo');
            }

            $response->statusCode = 200;
        } elseif ($controller == 'v1/upay') {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $data                       = [
                'inp' => Yii::$app->request->getRawBody(),
                'out' => $response->data,
            ];
            Yii::error(print_r($data, true), 'application');
        }
    }
}