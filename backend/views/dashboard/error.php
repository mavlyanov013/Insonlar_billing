<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = 'Error';
?>
<div class="site-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">
        <button class="close" type="button" data-dismiss="alert"
                aria-hidden="true">&times;</button>
        <?= nl2br(Html::encode($exception->getMessage())) ?>
    </div>
    <pre><?= $exception->getTraceAsString() ?></pre>
    <p>
        The above error occurred while the Web server was processing your request.
    </p>

    <p>
        Please contact us if you think this is a server error. Thank you.
    </p>

</div>
