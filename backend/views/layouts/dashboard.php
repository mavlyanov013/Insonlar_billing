<?php
/**
 * $this backend\components\View
 */
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;

?>
<?php $this->beginContent('@backend/views/layouts/main.php'); ?>
    <div class="wrap">
        <?php
        NavBar::begin([
                          'brandLabel' => 'ABT',
                          'brandUrl'   => Yii::$app->homeUrl,
                          'options'    => [
                              'class' => 'navbar navbar-fixed-top',
                          ],
                      ]);

        echo Nav::widget([
                             'options' => ['class' => 'navbar-nav'],
                             'items'   => $this->getMenuItems(),
                         ]);


        echo Nav::widget([
                             'options' => ['class' => 'navbar-nav navbar-right'],
                             'items'   => (Yii::$app->user->isGuest) ?
                                 [['label' => 'Login', 'url' => ['/backend/dashboard/login']]] :
                                 [
                                     [
                                         'label' => Yii::$app->user->identity->fullname,
                                         'url'   => ['/backend/dashboard/logout'],
                                         'items' => [
                                             [
                                                 'label' => __('My Profile'),
                                                 'url'   => ['/backend/dashboard/profile'],
                                             ],
                                             [
                                                 'label' => __('Logout'),
                                                 'url'   => ['/backend/dashboard/logout'],
                                             ],
                                         ],
                                     ],
                                 ],
                         ]);
        NavBar::end();
        ?>

        <div class="container">
            <?= Breadcrumbs::widget([
                                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                                    ]) ?>
            <?= $content ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p class="pull-left">&copy;ActiveMedia <?= date('Y') ?></p>

            <p class="pull-right"><?= Yii::powered(); ?> <?= Yii::getVersion() ?> | Bootstrap <a
                    href="http://fezvrasta.github.io/bootstrap-material-design/">Material Design</a></p>
        </div>
    </footer>
<?php $this->endContent(); ?>
