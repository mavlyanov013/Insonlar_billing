<?php
/**
 * @link      http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

namespace common\widgets;

use yii\base\Widget;

class Alert extends Widget
{
    public function run()
    {
        $session  = \Yii::$app->session;
        $messages = $session->getAllFlashes();
        if ($messages && is_array($messages)) {
            return $this->render('alert', [
                'messages' => $messages,
            ]);
        }
        return '';
    }
}
