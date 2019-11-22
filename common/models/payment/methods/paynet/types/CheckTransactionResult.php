<?php
namespace common\models\payment\methods\paynet\types;


class CheckTransactionResult extends GenericResult
{

    /**
     * @access public
     * @var integer
     */
    public $providerTrnId;

    /**
     * @access public
     * @var integer
     */
    public $transactionState;
    public $transactionStateErrorStatus;
    public $transactionStateErrorMsg;

}