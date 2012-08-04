<?php

namespace SpiffySecurity\Provider\NestedSet\Lazy;

use DomainException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use SpiffySecurity\Provider\Event;
use SpiffySecurity\Provider\ProviderInterface;
use SpiffySecurity\Rbac\Rbac;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class DoctrineDbal implements ProviderInterface
{
    /**
     * Simple array cache so that resources aren't queried more than once per request.
     * This should probably be expanded to a real cache (Zend\Cache) in the future.
     *
     * @var array
     */
    protected $cache;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var array
     */
    protected $roles;

    /**
     * @var DoctrineDbalOptions
     */
    protected $options;

    /**
     * Factory to create the provider.
     *
     * @static
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param array $spec
     * @return DoctrineDBAL
     */
    public static function factory(ServiceLocatorInterface $sl, array $spec)
    {
        $adapter = isset($spec['connection']) ? $spec['connection'] : null;
        if (!$adapter) {
            throw new DomainException('Missing required parameter: connection');
        }

        $options = isset($spec['options']) ? (array) $spec['options'] : array();
        if (!is_string($adapter) || $sl->has($adapter)) {
            $adapter = $sl->get($adapter);
        } else {
            throw new DomainException('Failed to find DBAL Connection');
        }

        return new DoctrineDbal($adapter, $options);
    }

    /**
     * @param Connection $connection
     * @param array $options
     */
    public function __construct(connection $connection, array $options)
    {
        $this->connection = $connection;
        $this->options    = new DoctrineDbalOptions($options);
    }

    /**
     * Attach to the listeners.
     *
     * @param \Zend\EventManager\EventManager $events
     * @return void
     */
    public function attachListeners(EventManager $events)
    {
        $events->attach(Event::EVENT_HAS_ROLE, array($this, 'hasRole'));
        $events->attach(Event::EVENT_IS_GRANTED, array($this, 'isGranted'));
    }

    /**
     * Loads roles for an identity.
     *
     * @param \SpiffySecurity\Provider\Event $e
     */
    public function hasRole(Event $e)
    {
        $rbac = $e->getRbac();
        $role = $e->getRole();

        if (!$role || $rbac->hasRole($role)) {
            return;
        }

        $this->load($rbac, $role);
    }

    /**
     * Load permissions into roles.
     *
     * @abstract
     * @param Event $e
     * @return void
     */
    public function isGranted(Event $e)
    {
        $granted    = false;
        $rbac       = $e->getRbac();
        $role       = $e->getRole();
        $permission = $e->getPermission();

        if ($rbac->hasRole($role) && $rbac->getRole($role)->hasPermission($permission)) {
            return;
        }

        $this->load($rbac, $role, $permission);
    }

    /**
     * Load the requested resources into RBAC.
     *
     * @param Rbac $rbac
     * @param string $role
     * @param string|null $permission
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function load($rbac, $role, $permission = null)
    {
        $options = $this->options;
        $builder = new QueryBuilder($this->connection);

        // Role always present
        $builder->select('node.name')
                ->from($options->getRoleTable(), 'node')
                ->from($options->getRoleTable(), 'parent')
                ->where('node.lft BETWEEN parent.lft AND parent.rgt')
                ->andWhere('parent.name = :role')
                ->orderBy('node.lft');

        $builder->setParameter('role', $role);

        // Permission optional
        if ($permission) {
            $builder->addSelect('permission.name AS permission')
                    ->leftJoin('node', 'role_permission', 'rp', 'node.id = rp.role_id')
                    ->leftJoin('node', 'permission', 'permission', 'rp.permission_id = permission.id')
                    ->andWhere('(permission.name = :permission OR permission.name IS NULL)');

            $builder->setParameter('permission', $permission);
        }

        $parent = null;
        foreach($builder->execute() as $row) {
            if ($parent) {
                if (!$rbac->hasRole($row['name'])) {
                    $rbac->getRole($parent)->addChild($row['name']);
                }
            } else if (!$rbac->hasRole($row['name'])) {
                $rbac->addRole($row['name']);
            }

            if ($permission) {
                if ($row['permission']) {
                    $rbac->getRole($row['name'])->addPermission($row['permission']);
                }
            }

            $parent = $row['name'];
        }

        return $builder;
    }
}