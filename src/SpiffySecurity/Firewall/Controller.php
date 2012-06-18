<?php

namespace SpiffySecurity\Firewall;

class Controller extends AbstractFirewall
{
    public function getRules()
    {
        $resources = array();
        foreach($this->options as $rule) {
            $resource = "controller:{$rule['controller']}";
            if (isset($rule['action'])) {
                $resource = "controller:{$rule['controller']}:{$rule['action']}";
            }

            $resources[] = array(
                'resource' => $resource,
                'roles'    => is_array($rule['roles']) ? $rule['roles'] : array($rule['roles'])
            );
        }

        return $resources;
    }
}
