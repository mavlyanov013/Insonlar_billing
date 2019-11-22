<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

/**
 * @var $model \common\models\Volunteer
 * @var $index int
 */
$colors = [
    'red',
    'purple',
    'blue',
    'orange',
    'riptide',
    'yellow',
    'green',
    'navy-blue',
];

$image = ($model->image) ? $model->getCroppedImage(300, 300) : $this->getImageUrl('events/event-1.jpg');
?>

<div class="col-md-4 col-lg-3 col-sm-6">
    <div class="xs-single-team xs-mb-5">
        <img src="<?= $model->getCroppedImage(255, 340) ?>" alt="">
        <div class="xs-team-content">
            <h4><?= str_replace(" ", "<br>", $model->fullname) ?></h4>
            <p><?= $model->job ?></p>
            <svg class="xs-svgs" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 270 138">
                <path class="fill-<?= isset($colors[$index % 8]) ? $colors[$index % 8] : $colors[0] ?>"
                      d="M375,3294H645v128a10,10,0,0,1-10,10l-250-20a10,10,0,0,1-10-10V3294Z"
                      transform="translate(-375 -3294)"></path>
            </svg>
        </div><!-- .xs-team-content END -->
    </div>
</div>
