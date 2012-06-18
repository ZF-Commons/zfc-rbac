<?php

namespace SpiffySecurity\Firewall;

class Route extends AbstractFirewall
{
    public function getRules()
    {
        $resources = array();
        foreach($this->options as $rule) {
            $resources[] = array(
                'resource' => "route:{$rule['route']}",
                'roles'    => is_array($rule['roles']) ? $rule['roles'] : array($rule['roles'])
            );
        }

        return $resources;
    }
}
