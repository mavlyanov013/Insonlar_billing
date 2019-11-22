<?php

/* @var $this View */

/* @var $content string */

use common\components\Config;
use frontend\assets\AppAsset;
use frontend\components\View;
use yii\helpers\Html;

AppAsset::register($this);

$url   = $this->getCanonical();
$title = Html::encode($this->title ? $this->title : __('Saxovat Qo\'qon Xayriya jamoat fondi'));

if (!$this->hasDescription())
    $this->addDescription([__('Xalqdan xalqqa! Maqsadimiz - muhtojlarga yordam berish.')]);
if (!$this->hasKeywords())
    $this->addKeywords([__('saxovat qo\'qon, saxovat quqon, xayriya, hayriya, saxovat, sahovat, yordam berish, moddiy yordam, ezgulik, yaxshilik, ezgu amal')]);

$description = $this->getDescription();
$keywords    = $this->getKeywords();
$this->registerCsrfMetaTags();
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Config::getHtmlLangSpec(Yii::$app->language) ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <meta property="og:url" content="<?= $url ?>">
    <meta property="og:title" content="<?= $title ?>">
    <meta property="og:description" content="<?= $description ?>">
    <meta property="og:image" content="<?= $this->getImage() ?>">
    <meta property="og:type" content="article"/>

    <meta name="description" content="<?= $description ?>">
    <meta name="keywords" content="<?= $keywords ?>">

    <title><?= $title ?> - saxovat.uz</title>
    <link rel="canonical" href="<?= $url ?>"/>

    <link rel="apple-touch-icon" sizes="57x57" href="<?= $this->getImageUrl('/favicon/apple-icon-57x57.png') ?>">
    <link rel="apple-touch-icon" sizes="60x60" href="<?= $this->getImageUrl('/favicon/apple-icon-60x60.png') ?>">
    <link rel="apple-touch-icon" sizes="72x72" href="<?= $this->getImageUrl('/favicon/apple-icon-72x72.png') ?>">
    <link rel="apple-touch-icon" sizes="76x76" href="<?= $this->getImageUrl('/favicon/apple-icon-76x76.png') ?>">
    <link rel="apple-touch-icon" sizes="114x114" href="<?= $this->getImageUrl('/favicon/apple-icon-114x114.png') ?>">
    <link rel="apple-touch-icon" sizes="120x120" href="<?= $this->getImageUrl('/favicon/apple-icon-120x120.png') ?>">
    <link rel="apple-touch-icon" sizes="144x144" href="<?= $this->getImageUrl('/favicon/apple-icon-144x144.png') ?>">
    <link rel="apple-touch-icon" sizes="152x152" href="<?= $this->getImageUrl('/favicon/apple-icon-152x152.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $this->getImageUrl('/favicon/apple-icon-180x180.png') ?>">
    <link rel="icon" type="image/png" sizes="192x192"
          href="<?= $this->getImageUrl('/favicon/android-icon-192x192.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $this->getImageUrl('/favicon/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="96x96" href="<?= $this->getImageUrl('/favicon/favicon-96x96.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $this->getImageUrl('/favicon/favicon-16x16.png') ?>">
    <link rel="manifest" href="<?= $this->getImageUrl('/favicon/manifest.json') ?>">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?= $this->getImageUrl('/favicon/ms-icon-144x144.png') ?>">
    <meta name="theme-color" content="#1969D6">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,500,600,700&subset=cyrillic" rel="stylesheet">
    <?php $this->head() ?>
</head>
<body>

<?php $this->beginBody() ?>

<?= $content ?>
<?php $this->endBody() ?>
<?php if (!YII_DEBUG): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-111137764-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());
        gtag('config', 'UA-111137764-1', {
            cookie_domain: 'blog.xabar.uz'
        });
    </script>
<?php endif; ?>
</body>
</html>
<?php $this->endPage() ?>
