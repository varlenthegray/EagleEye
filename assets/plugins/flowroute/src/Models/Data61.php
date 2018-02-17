<?php
/*
 * FlowrouteNumbersAndMessagingLib
 *
 * This file was automatically generated by APIMATIC v2.0 ( https://apimatic.io ).
 */

namespace FlowrouteNumbersAndMessagingLib\Models;

use JsonSerializable;

/**
 * @todo Write general description for this model
 */
class Data61 implements JsonSerializable
{
    /**
     * @todo Write general description for this property
     * @var string|null $type public property
     */
    public $type;

    /**
     * @todo Write general description for this property
     * @var \FlowrouteNumbersAndMessagingLib\Models\Attributes62|null $attributes public property
     */
    public $attributes;

    /**
     * Constructor to set initial or default values of member properties
     * @param string       $type       Initialization value for $this->type
     * @param Attributes62 $attributes Initialization value for $this->attributes
     */
    public function __construct()
    {
        switch (func_num_args()) {
            case 2:
                $this->type       = func_get_arg(0);
                $this->attributes = func_get_arg(1);
                break;

            default:
                $this->type = 'route';
                break;
        }
    }


    /**
     * Encode this object to JSON
     */
    public function jsonSerialize()
    {
        $json = array();
        $json['type']       = $this->type;
        $json['attributes'] = $this->attributes;

        return $json;
    }
}