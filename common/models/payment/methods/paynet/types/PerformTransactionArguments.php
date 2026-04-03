<?php
namespace common\models\payment\methods\paynet\types;


use DateTime;

class PerformTransactionArguments extends GenericArguments
{

    /**
     * @access public
     * @var integer
     */
    public $amount;

    /**
     * @access public
     * @var GenericParam[]
     */
    public $serviceId;

    /**
     * @access public
     * @var integer
     */
    public $transactionId;

    /**
     * @access public
     * @var dateTime
     */
    public $transactionTime;

}