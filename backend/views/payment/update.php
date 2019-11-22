<?php

use common\models\payment\Payment;

/**
 * @var $this  yii\web\View
 * @var $model Payment
 */

$this->title                   = $model->amount . " ($model->user_data)";
$this->params['breadcrumbs'][] = ['url' => ['payment/index'], 'label' => __('Manage Payments')];

$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create">
    <div class="row">
        <div class="col col-md-5">
            <div class="panel">
                <div class="panel-heading">
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <tr>
                            <th><?= $model->getAttributeLabel('user_data') ?></th>
                            <td><?= $model->user_data ?></td>
                        </tr>
                        <tr>
                            <th><?= $model->getAttributeLabel('amount') ?></th>
                            <td><strong><?= $model->amount ?></strong></td>
                        </tr>
                        <tr>
                            <th><?= $model->getAttributeLabel('time') ?></th>
                            <td><?= $model->getPaymentDateFormatted() ?></td>
                        </tr>
                        <tr>
                            <th><?= $model->getAttributeLabel('method') ?></th>
                            <td><?= $model->getMethodLabel() ?></td>
                        </tr>
                        <tr>
                            <th><?= $model->getAttributeLabel('status') ?></th>
                            <td><?= $model->getStatusLabel() ?></td>
                        </tr>
                        <tr>
                            <th><?= $model->getAttributeLabel('transaction_id') ?></th>
                            <td><?= $model->transaction_id ?></td>
                        </tr>
                        <tr>
                            <th><?= $model->getAttributeLabel('click_paydoc_id') ?></th>
                            <td><?= $model->click_paydoc_id ?></td>
                        </tr>
                        <tr>
                            <th><?= $model->getAttributeLabel('created_at') ?></th>
                            <td><?= Yii::$app->formatter->asDatetime($model->created_at->getTimestamp()) ?></td>
                        </tr>
                        <tr>
                            <th><?= $model->getAttributeLabel('updated_at') ?></th>
                            <td><?= Yii::$app->formatter->asDatetime($model->updated_at->getTimestamp()) ?></td>
                        </tr>
                        <?php if ($model->cancel_time): ?>
                            <tr>
                                <th><?= $model->getAttributeLabel('cancel_time') ?></th>
                                <td><?= Yii::$app->formatter->asDatetime($model->cancel_time / 1000) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th><?= $model->getAttributeLabel('live_mode') ?></th>
                            <td><?= __($model->live_mode ? 'Yes' : 'No') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col col-md-7">
            <?php if (YII_DEBUG): ?>
                <div class="panel">
                    <div class="panel-body">
                    <pre><?= json_encode($model->getAttributes(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?>
                    </pre>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>