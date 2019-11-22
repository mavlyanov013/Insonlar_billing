<?php

/* @var $this \frontend\components\View */

/* @var $model \common\models\Page */

use yii\widgets\Pjax;

$method = Yii::$app->request->get('method');

?>
<div id="st-container" class="st-container">
    <section class="slice-lg has-bg-cover bg-size-cover"
             style="min-height: 100vh;background-image: url(<?= $this->getImageUrl('bg-1.jpg') ?>);background-size: cover; background-position: center center;">
        <div class="container">
            <div class="row justify-content-center cols-xs-space">
                <div class="col-lg-7 d-xs-none"></div>
                <div class="col-lg-5 col-md-9">
                    <div class="form-card form-card--style-2 z-depth-2-top">
                        <div class="form-header text-center">
                            <div class="form-header-icon">
                                <h1>
                                    <?= $model->title ?>
                                </h1>
                            </div>
                        </div>
                        <div class="form-body" style="min-height: 100vh;">
                            <?= $model->content ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">

            </div>
        </div>
    </section>
</div>