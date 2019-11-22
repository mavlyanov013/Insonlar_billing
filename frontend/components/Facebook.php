<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2018. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace frontend\components;

use ymaker\social\share\base\Driver;

class Facebook extends Driver
{
    /**
     * @inheritdoc
     */
    public function getLink()
    {
        $this->_link = 'http://www.facebook.com/sharer.php?u={url}';
        $this->_metaTags = [
            //['property' => 'og:url',         'content' => '{url}'],
            //['property' => 'og:type',        'content' => 'website'],
            //['property' => 'og:title',       'content' => '{title}'],
            //['property' => 'og:description', 'content' => '{description}'],
            //['property' => 'og:image',       'content' => '{imageUrl}'],
        ];

        return parent::getLink();
    }

    /**
     * @inheritdoc
     */
    protected function processShareData()
    {
        $this->url         = static::encodeData($this->url);
        $this->description = static::encodeData($this->description);
    }
}
