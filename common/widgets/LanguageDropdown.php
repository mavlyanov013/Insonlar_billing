<?php

namespace common\widgets;

use common\components\Config;
use Yii;
use yii\bootstrap\Dropdown;

class LanguageDropdown extends Dropdown
{
    private static $_labels;

    private $_isError;
    public  $activeClass = 'active';
    public  $isShort     = false;
    public  $langs       = ['uz' => 'uz-UZ', 'k' => 'cy-UZ'];

    public function init()
    {
        $route          = Yii::$app->controller->route;
        $appLanguage    = Yii::$app->language;
        $params         = $_GET;
        $this->_isError = $route === Yii::$app->errorHandler->errorAction;

        array_unshift($params, '/' . $route);

        foreach ($this->langs as $language) {
            $active = $language === $appLanguage || substr($appLanguage, 0, 2) === substr($language, 0, 2);

            if ($active) {
                if (isset($this->options['exclude']) && $this->options['exclude'])
                    continue;
            }

            $params['language'] = $language;

            $this->items[] = [
                'label'   => $this->label($language),
                'url'     => $params,
                'encode'  => false,
                'options' => [
                    'class' => $active ? $this->activeClass : '',
                ],
            ];
        }
        parent::init();
    }

    public function run()
    {
        if ($this->_isError) {
            return '';
        } else {
            return parent::run();
        }
    }

    public function label($code)
    {
        if (self::$_labels === null) {
            self::$_labels = $this->isShort ? Config::getLanguageOptionsWithShortLabel() : Config::getLanguageOptions();
        }

        return isset(self::$_labels[$code]) ? self::$_labels[$code] : $code;
    }
}