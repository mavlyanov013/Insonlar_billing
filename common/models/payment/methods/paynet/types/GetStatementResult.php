<?php
namespace common\models\payment\methods\paynet\types;


class GetStatementResult extends GenericResult
{

    /**
     * @access public
     * @var TransactionStatement[]
     */
    public $statements;

}