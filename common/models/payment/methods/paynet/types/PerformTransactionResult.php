<?php
namespace common\models\payment\methods\paynet\types;


class PerformTransactionResult extends GenericResult
{

    /**
     * @access public
     * @var GenericParam[]
     */
    public $parameters;

    /**
     * @access public
     * @var integer
     */
    public $providerTrnId;

}