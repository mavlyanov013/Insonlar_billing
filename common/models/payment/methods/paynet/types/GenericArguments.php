<?php
namespace common\models\payment\methods\paynet\types;


abstract class GenericArguments
{
    /**
     * @access public
     * @var string
     */
    public $password;

    /**
     * @access public
     * @var string
     */
    public $username;

    /**
     * @var GenericParam | GenericParam[]
     */
    public $parameters;

    public $serviceId;

    public static function create(\StdClass $data)
    {
        $class = new static();

        foreach ($data as $key => $value) {
            if (property_exists($class, $key)) {
                $class->{$key} = $value;
            }
        }

        return $class;
    }

    public function getUserKey()
    {
        $userKey = "";
        if (is_array($this->parameters) && isset($this->parameters[0])) {
            $userKey = $this->parameters[0]->paramKey;
        } elseif (is_object($this->parameters)) {
            $userKey = $this->parameters->paramKey;
        }

        return $userKey;
    }

    public function getUserData()
    {
        $userData = "";
        if (is_array($this->parameters) && isset($this->parameters[0])) {
            $userData = $this->parameters[0]->paramValue;
        } elseif (is_object($this->parameters)) {
            $userData = $this->parameters->paramValue;
        }

        return $userData;
    }

    public function getParamsAsArray()
    {
        $userData = [];
        if (is_array($this->parameters)) {
            foreach ($this->parameters as $i => $data)
                $userData[$data->paramKey] = $data->paramValue;
        } elseif (is_object($this->parameters)) {
            $userData[$this->parameters->paramKey] = $this->parameters->paramValue;
        }

        return $userData;
    }


}