<?php
/**
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */

namespace frontend\components;

use ymaker\social\share\base\Driver;

class Odnoklassniki extends Driver
{
    /**
     * @inheritdoc
     */
    public function getLink()
    {
        $this->_link = 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1'
            . '&st._surl={url}'
            . '&st.comments={description}';

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
