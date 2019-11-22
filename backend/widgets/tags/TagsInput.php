<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

/**
 * Created by PhpStorm.
 * Date: 11/9/17
 * Time: 9:52 PM
 */

namespace backend\widgets\tags;


use yii\helpers\Html;
use yii\helpers\Json;
use yii\jui\InputWidget;

class TagsInput extends InputWidget
{
    public $clientOptions = [];

    public function run()
    {
        $view = $this->getView();
        TagsInputAsset::register($view);
        $this->registerClientOptions('tagsInput', $this->options['id']);
        echo $this->renderWidget();
    }


    /**
     * Renders the TagsInput widget.
     * @return string the rendering result.
     */
    public function renderWidget()
    {
        if ($this->hasModel()) {
            return Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            return Html::textInput($this->name, $this->value, $this->options);
        }
    }

}