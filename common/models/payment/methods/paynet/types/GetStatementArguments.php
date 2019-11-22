<?php
namespace common\models\payment\methods\paynet\types;


class GetStatementArguments extends GenericArguments
{

    /**
     * @access public
     * @var string
     */
    public $dateFrom;

    /**
     * @access public
     * @var string
     */
    public $dateTo;

    /**
     * @access public
     * @var integer
     */
    public $serviceId;

    public $onlyTransactionId;

}