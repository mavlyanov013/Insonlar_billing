<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

use frontend\components\View;

/**
 * @var $this    View
 * @var $content string
 */
$this->beginContent('@app/views/layouts/main.php');
?>
<div class="body-wrap">
    <?= $this->renderFile('@app/views/layouts/header.php') ?>
    <?= $content ?>
    <?= $this->renderFile('@app/views/layouts/footer.php') ?>
</div>

<div id="ajax_content"></div>
<?php $this->endContent() ?>
