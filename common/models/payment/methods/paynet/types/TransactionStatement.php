<?php
namespace common\models\payment\methods\paynet\types;


class TransactionStatement
{

    /**
     * @access public
     * @var integer
     */
    public $amount;

    /**
     * @access public
     * @var integer
     */
    public $providerTrnId;

    /**
     * @access public
     * @var integer
     */
    public $transactionId;

    /**
     * @access public
     * @var string
     */
    public $transactionTime;

}