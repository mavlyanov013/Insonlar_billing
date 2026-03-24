<?php

/* @var $this \frontend\components\View */

use yii\widgets\Pjax;

$method = Yii::$app->request->get('method');

?>
<div id="st-container" class="st-container">
    <section class="slice-lg has-bg-cover bg-size-cover"
             style="background-image: url(<?= $this->getImageUrl('bg-1.jpg') ?>);background-size: cover; background-position: center center;">
        <div class="container">
            <div class="row justify-content-center cols-xs-space">
                <div class="col-lg-7 d-xs-none"></div>
                <div class="col-lg-5 col-md-9">
                    <?php Pjax::begin(['id' => 'form-data', 'timeout' => false, 'options' => ['data-pjax' => false], 'enablePushState' => false]) ?>

                    <div class="form-card form-card--style-2 z-depth-2-top">
                        <div class="form-header text-center">
                            <div class="form-header-icon">
                                <h1>
                                    <?= __('"Saxovat Qo\'qon" xayriya jamoat fondiga pul o\'tkazish') ?>
                                </h1>
                            </div>
                        </div>
                        <div class="form-body">
                            <div class="text-center px-2">
                                <img class="logo" src="<?= $this->getImageUrl('favicon/favicon-96x96.png') ?>">
                            </div>

                            <?= \common\widgets\Alert::widget() ?>
                            <form data-pjax="1" method="GET" id="xs-donation-form" class="xs-donation-form">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label><?= __('O\'zingizni tanishtiring') ?></label>
                                            <input type="text"
                                                   maxlength="40"
                                                   value="<?= $name ?>"
                                                   name="name"
                                                   class="form-control form-control-lg"
                                                   placeholder="<?= __('Ism yoki telefon raqami') ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group has-feedback">
                                            <label for="xs-donate-amount"><?= __('Xayriya miqdori') ?>
                                                <span class="color-lightRed">*</span>
                                                <span class="values">
                                                    <a href="#5000" data-value="5000">5,000</a> -
                                                    <a href="#20000" data-value="20000">20,000</a> -
                                                    <a href="#50000" data-value="50000">50,000</a> -
                                                    <a href="#100000" data-value="100000">100,000</a>
                                                </span>
                                            </label>
                                            <input maxlength="12" type="number" value="<?= $amount ? $amount : 5000 ?>"
                                                   name="amount"
                                                   id="donate-amount"
                                                   class="form-control form-control-lg"
                                                   placeholder="<?= __('Qancha pul o\'tkazamiz?') ?>">
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="method" value="paycom" style="display: none">
                                <button type="submit" class="btn btn-styled btn-lg btn-block btn-base-1 mt-4">
                                    <?= __('To\'lov') ?>
                                </button>

                                <div class="notes">
                                    <ul class="footer-menu">
                                        <li><a data-pjax="0"
                                               href="<?= linkTo(['page/contacts']) ?>"><?= __('Contacts') ?></a></li>
                                        <li><a data-pjax="0"
                                               href="<?= linkTo(['page/return-policy']) ?>"><?= __('Return Policy') ?></a>
                                        </li>
                                        <li><a data-pjax="0"
                                               href="<?= linkTo(['page/terms']) ?>"><?= __('Terms and Conditions') ?></a>
                                        </li>
                                    </ul>
                                    <p class="pt-3">
                                        <img width="260" class='image-responsive'
                                             src="<?= $this->getImageUrl('pay-logos.png') ?>"/>
                                    </p>

                                    <p class="pt-1">
                                        <?= __('Developed by {link}', ['link' => \yii\helpers\Html::a(__('ActiveMedia Solutions'), 'http://activemedia.uz/', ['data-pjax' => 0])]) ?>
                                    </p>

                                    <p style="margin: 12px 0 -12px">
                                        "SOLGAR"
                                        121170, г. Москва, Кутузовский Проспект, д.36, строение 3, офис 315 Б
                                        Тел. : +7 775 678 60 01, +7 775 678 59 00
                                    </p>

                                </div>
                            </form>
                        </div>
                    </div>

                    <?php

                    $this->registerJs("initPayment()");
                    ?>
                    <?php if (isset($result) && $result): ?>
                        <script type="application/javascript">
                            var sendForm = function (data) {
                                var form = $("<form>", {
                                    "action": data.action,
                                    "method": "post"
                                });
                                for (var key in data.form) {
                                    if (data.form.hasOwnProperty(key))
                                        form.append($("<input>", {
                                            "name": key,
                                            "value": data.form[key],
                                            "type": "hidden"
                                        }));
                                }
                                form.appendTo("body").submit();
                            };
                            <?php if(Yii::$app->request->isAjax):?>
                            setTimeout(function () {
                                sendForm(<?=json_encode($result)?>);
                            }, 3000);
                            <?php else:?>
                            <?php $this->registerJs("sendForm(" . json_encode($result) . ");");?>
                            <?php endif;?>
                        </script>
                    <?php endif; ?>

                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </section>
</div>