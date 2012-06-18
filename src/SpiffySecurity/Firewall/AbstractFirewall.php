<?php

namespace SpiffySecurity\Firewall;

abstract class AbstractFirewall
{
    protected $options = array();

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    abstract public function getRules();
}