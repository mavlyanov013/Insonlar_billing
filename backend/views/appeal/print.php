<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use backend\components\View;
use common\models\Appeal;

/* @var $this View */
/* @var $model common\models\Appeal */

$this->title = $model->number;
$user        = $this->context->_user();
$comments    = \common\models\AppealHistory::findByAppeal($model);
$statuses    = Appeal::getStatusOptions()
?>
<style type="text/css" media="print">
    @page {
        size: auto;   /* auto is the initial value */
        margin: 0 20px;  /* this affects the margin in the printer settings */
    }
</style>
<script>
     window.print();
</script>
<div class="container">
    <div class="user-form">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">

                    <div class="panel-body">
                        <img src="/img/mehrli-logo.png" width="260px">
                        <p><br></p>
                        <table class="table table-striped">
                            <tbody>
                            <tr>
                                <th width="20%"><?= __('Ariza raqami') ?>:</th>
                                <td><?= $model->number ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Ism-sharifi') ?>:</th>
                                <td><?= $model->fullname ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Tug\'ilgan yili') ?>:</th>
                                <td><?= $model->year ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Tashxisi') ?>:</th>
                                <td><?= $model->diagnose ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Manzili') ?>:</th>
                                <td><?= $model->address ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Telefon') ?>:</th>
                                <td><?= $model->phone ?>; <?= $model->phone2 ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Email') ?>:</th>
                                <td><?= $model->email ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading text-center">
                        <h2><?= __('Ariza') ?></h2>
                    </div>
                    <div class="panel-body">
                        <?= nl2br($model->text) ?>
                    </div>

                    <div class="panel-footer">
                        <p>&nbsp;</p>
                        <table width="100%">
                            <tr>
                                <td width="60%">
                                    <?= __('Sana') ?>
                                    : <?= Yii::$app->formatter->asDate($model->created_at->getTimestamp(), 'php:d F, Y') ?>
                                </td>
                                <td style="text-align: right">
                                    <?= __('Imzo') ?>
                                    : ____________________

                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
