<?php
/**
 * Created by PhpStorm.
 * User: abdujabbor
 * Date: 9/4/16
 * Time: 5:55 PM
 */

namespace common\components;


use yii\grid\CheckboxColumn;
use yii\helpers\Html;
use yii\helpers\Json;


class RadioColumn extends CheckboxColumn
{
    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->checkboxOptions instanceof Closure) {
            $options = call_user_func($this->checkboxOptions, $model, $key, $index, $this);
        } else {
            $options = $this->checkboxOptions;
        }

        if (!isset($options['value'])) {
            $options['value'] = is_array($key) ? Json::encode($key) : $key;
        }

        if ($this->cssClass !== null) {
            Html::addCssClass($options, $this->cssClass);
        }

        return Html::radio($this->name, !empty($options['checked']), $options);
    }
}