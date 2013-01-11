<?php
/**
 * A view helper to check for identity role
 * package ZfcRbac
 */
namespace ZfcRbac\View\Helper;

use ZfcRbac\Identity\IdentityInterface;
use Zend\View\Helper\AbstractHelper;

class HasRole extends AbstractHelper
{
    /**
     * @var IdentityInterface
     */
    protected $_identity;

    /**
     * @param RbacService $rbacService
     */
    public function __construct(IdentityInterface $identity)
    {
        $this->_identity = $identity;
    }

    /**
     * @param string $role
     * @return bool
     */
    public function __invoke($role)
    {
        return $this->_identity->hasRole($role);
    }
}