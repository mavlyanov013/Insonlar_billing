<?php

namespace backend\widgets;

class GridView extends \yii\grid\GridView
{
    public $options      = ['class' => 'table-responsive'];
    public $layout       = "{items}\n<div class='panel-footer'>{pager}<div class='clearfix'></div></div>";
    public $tableOptions = ['class' => 'table table-striped table-hover '];
}