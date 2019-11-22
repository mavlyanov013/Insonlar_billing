<?php
namespace common\models\payment\methods\paynet\types;


class CancelTransactionArguments  extends GenericArguments
{
    /**
     * @access public
     * @var integer
     */
    public $serviceId;

    /**
     * @access public
     * @var integer
     */
    public $transactionId;
}