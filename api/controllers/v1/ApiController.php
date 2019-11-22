<?php

namespace api\controllers\v1;


use common\components\Config;
use Yii;

class ApiController extends BaseController
{
    protected $_user = false;
    protected $_postData;

    public function beforeAction($action)
    {
        $languages = Config::getLanguageOptions();

        if (($lang = $this->get('l')) && isset($languages[$lang])) {
            Yii::$app->language = $lang;
        }
        $this->_postData = Yii::$app->request->post();

        return parent::beforeAction($action);
    }

    protected function getBodyData()
    {
        return $this->jsonDecode(Yii::$app->request->getRawBody(), true);
    }

    protected function jsonDecode($json, $assoc = false, $depth = 512, $options = 0)
    {
        if ($json) {
            $data = json_decode($json, $assoc, $depth, $options);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \InvalidArgumentException('json_decode error: ' . json_last_error_msg());
            }

            return $data;
        }

        return [];
    }


}