<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

/**
 * Created by PhpStorm.
 * User: rustam
 * Date: 6/27/18
 * Time: 2:57 PM
 */

namespace frontend\widgets;


use common\models\Page;
use yii\base\InvalidConfigException;

class BlockWidget extends BaseWidget
{
    public $block;

    public function init()
    {
        if (empty($this->block)) {
            throw new InvalidConfigException('The $block property must be set.');
        }
        parent::init();
    }

    public function run()
    {
        $block = Page::getStaticBlock($this->block, true);
        if ($block != null) {
            $content = preg_replace_callback('/({{)(.*)(}})/', function ($matches) {
                return $this->normalizeImageUrl($matches[2]);
            }, $block->content);

            return $content;
        }
        $message = __('Block \'{block}\' not found.', ['block' => $this->block]);
        return "<div class='container text-center'><code>{$message}</code></div>";
    }

    protected function normalizeImageUrl($name)
    {
        if (strpos('http', $name) === 0) {
            return $name;
        }
        $name = str_replace('assets/images/', '', $name);
        return $this->getImageUrl($name);
    }
}