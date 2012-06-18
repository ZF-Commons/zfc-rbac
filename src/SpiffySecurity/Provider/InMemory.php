<?php

namespace SpiffySecurity\Provider;

class InMemory extends AbstractProvider
{
    public function getRoles()
    {
        return $this->options;
    }
}
