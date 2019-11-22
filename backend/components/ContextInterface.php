<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace backend\components;

use yii\base\ViewContextInterface;
use yii\web\IdentityInterface;

interface ContextInterface extends ViewContextInterface
{
    /**
     * @return IdentityInterface
     */
    public function _user();

}