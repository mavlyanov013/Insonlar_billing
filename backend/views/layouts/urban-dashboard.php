<?php
/**
 * @var $this backend\components\View
 */

use common\widgets\LanguageDropdown;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>
<?php $this->beginContent('@backend/views/layouts/urban-main.php'); ?>
    <!-- quick launch panel -->
    <div class="quick-launch-panel">
        <div class="container">
            <div class="quick-launcher-inner">
                <a href="javascript:;" class="close" data-toggle="quick-launch-panel">×</a>

                <div class="css-table-xs">
                    <div class="col">
                        <a href="app-calendar.html">
                            <i class="icon-marquee"></i>
                            <span>Calendar</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="app-gallery.html">
                            <i class="icon-drop"></i>
                            <span>Gallery</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="app-messages.html">
                            <i class="icon-mail"></i>
                            <span>Messages</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="app-social.html">
                            <i class="icon-speech-bubble"></i>
                            <span>Social</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="charts-flot.html">
                            <i class="icon-pie-graph"></i>
                            <span>Analytics</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="javascript:;">
                            <i class="icon-esc"></i>
                            <span>Documentation</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /quick launch panel -->
    <div
            class="app layout-fixed-header <?= isset($_COOKIE['sm_menu']) && $_COOKIE['sm_menu'] ? 'layout-small-menu' : '' ?>">
        <!-- sidebar panel -->
        <div class="sidebar-panel offscreen-left">

            <div class="brand">
                <a href="javascript:;" class="toggle-sidebar hidden-xs hamburger-icon v3"
                   data-toggle="layout-small-menu">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </a>
            </div>

            <!-- main navigation -->
            <nav class="ps-container ps-theme-default" role="navigation">
                <ul class="nav">
                    <?php
                    $activeMenu = Yii::$app->controller->activeMenu; ?>
                    <?php foreach ($this->getMenuItems() as $id => $item): ?>
                        <?php $hasChild = isset($item['items']) && count($item['items']); ?>
                        <?php $active = $id == $activeMenu ?>
                        <li class="<?= $hasChild ? 'menu-accordion' : '' ?> <?= $active ? 'active open' : '' ?>">
                            <a href="<?= $item['url'] ?>">
                                <i class="fa fa-<?= $item['icon'] ?>"></i>
                                <span><?= $item['label'] ?></span>
                            </a>
                            <?php if ($hasChild): ?>
                                <ul class="sub-menu">
                                    <?php foreach ($item['items'] as $item): ?>
                                        <li class="<?= $item['url'] == '/' . Yii::$app->controller->route ? 'active' : '' ?>">
                                            <a href="<?= $item['url'] ?>">
                                                <span><?= $item['label'] ?></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>

        <!-- /sidebar panel -->
        <!-- content panel -->
        <div class="main-panel">

            <!-- top header -->
            <header class="header navbar">

                <div class="brand visible-xs">
                    <!-- toggle offscreen menu -->
                    <div class="toggle-offscreen">
                        <a href="#" class="hamburger-icon visible-xs" data-toggle="offscreen" data-move="ltr">
                            <span></span>
                            <span></span>
                            <span></span>
                        </a>
                    </div>
                    <!-- /toggle offscreen menu -->

                    <!-- logo -->
                    <div class="brand-logo">
                        URBAN THEME
                    </div>
                    <!-- /logo -->

                </div>

                <ul class="nav navbar-nav hidden-xs">
                    <li>
                        <p class="navbar-text">
                            <?= __('Dashboard') ?>
                        </p>
                    </li>
                </ul>
                <?php $menu = new LanguageDropdown(['langs' => ['uz' => 'uz-UZ']]); ?>
                <ul class="nav navbar-nav navbar-right hidden-xs">
                    <?php foreach ($menu->items as $item): ?>
                        <li class="<?= $item['options']['class'] ?>">
                            <a href="<?= Url::to($item['url']) ?>">
                                <?= $item['label'] ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li>
                        <a href="<?= Url::to(['system/cache']) ?>">
                            <i class="fa fa-refresh"></i>
                        </a>
                    </li>

                    <li>
                        <a data-toggle="dropdown">
                            <span><?= $this->_user()->getFullName() ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="<?= Url::to(['dashboard/profile']) ?>"><?= __('Profile') ?></a>
                            </li>
                            <li>
                                <a href="<?= Url::to(['dashboard/logout']) ?>"><?= __('Logout') ?></a>
                            </li>
                        </ul>

                    </li>
                </ul>
            </header>

            <div class="main-content">
                <?= Breadcrumbs::widget([
                    'options' => [
                        'class' => 'breadcrumb ',
                    ],
                    'links'   => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                ]) ?>
                <?php echo \common\widgets\Alert::widget() ?>

                <?= $content; ?>
                <div id="loader">
                    <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                </div>
            </div>
            <!-- /main area -->
        </div>
    </div>
<?php $this->endContent(); ?>