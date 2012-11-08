<?php

namespace ZfcRbacTest\Service\Asset\Firewall;

use ZfcRbac\Firewall\AbstractFirewall;

class SimpleFirewall extends AbstractFirewall
{
    /**
     * Get the firewall name.
     *
     * @return string
     */
    public function getName()
    {
        return 'simple-firewall';
    }

    /**
     * Checks if access is granted to resource for the role.
     *
     * @param string $resource
     * @return bool
     */
    public function isGranted($resource)
    {
        return true;
    }
}