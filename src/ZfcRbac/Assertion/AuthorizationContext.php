<?php
/**
 * Athene2 - Advanced Learning Resources Manager
 *
 * @author    Aeneas Rekkas (aeneas.rekkas@serlo.org)
 * @license   LGPL-3.0
 * @license   http://opensource.org/licenses/LGPL-3.0 The GNU Lesser General Public License, version 3.0
 * @link      https://github.com/serlo-org/athene2 for the canonical source repository
 * @copyright Copyright (c) 2013-2014 Gesellschaft für freie Bildung e.V. (http://www.open-education.eu/)
 */
namespace ZfcRbac\Assertion;

/**
 * A default implementation for AuthorizationContextInterface
 *
 * @author  Aeneas Rekkas
 * @licence MIT
 */
class AuthorizationContext implements AuthorizationContextInterface
{
    /**
     * @var string
     */
    protected $permission;

    /**
     * @var mixed
     */
    protected $context;

    /**
     * @param string $permission
     * @param mixed  $context
     */
    public function __construct($permission, $context = null)
    {
        $this->permission = $permission;
        $this->context    = $context;
    }

    /**
     * Get the permission
     *
     * @return string
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Get the context
     *
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }
}
