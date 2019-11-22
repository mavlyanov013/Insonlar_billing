<?php
/**
 * Created by PhpStorm.
 * User: shavkat
 * Date: 9/15/18
 * Time: 11:21 AM
 */

namespace api\components;


use yii\web\XmlResponseFormatter;

class ResponseFormatterXml extends XmlResponseFormatter
{
    public $contentType = 'text/xml';
}