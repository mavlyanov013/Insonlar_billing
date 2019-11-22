<?php
namespace common\models\payment\methods\paynet\types;


class GenericResult
{
    /**
     * @access public
     * @var string
     */
    public $errorMsg;

    /**
     * @access public
     * @var integer
     */
    public $status;

    /**
     * @access public
     * @var dateTime
     */
    public $timeStamp;
}