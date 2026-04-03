<?php
namespace common\models\payment\methods\paynet\types;


class GetInformationArguments extends GenericArguments
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
    public $serviceId;

}