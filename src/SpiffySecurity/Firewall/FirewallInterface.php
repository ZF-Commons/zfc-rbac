<?php

namespace SpiffySecurity\Firewall;

interface FirewallInterface
{
    /**
     * Get the firewall name.
     *
     * @abstract
     * @return string
     */
    public function getName();

    /**
     * Checks if access is granted to resource for the role.
     *
     * @abstract
     * @param string $role
     * @param string $resource
     * @return bool
     */
    public function isAllowed($role, $resource);
}