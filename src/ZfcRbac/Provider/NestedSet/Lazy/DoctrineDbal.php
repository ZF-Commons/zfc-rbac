<?php

namespace ZfcRbac\Provider\NestedSet\Lazy;

use DomainException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Zend\Permissions\Rbac\Rbac;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcRbac\Provider\AbstractProvider;
use ZfcRbac\Provider\Event;

class DoctrineDbal extends AbstractProvider
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
     * @param EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $events->attach(Event::EVENT_HAS_ROLE, array($this, 'hasRole'));
        $events->attach(Event::EVENT_IS_GRANTED, array($this, 'isGranted'));
    }

    /**
     * @param EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        $events->detach($this);
    }

    /**
     * Loads roles for an identity.
     *
     * @param Event $e
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
            } elseif (!$rbac->hasRole($row['name'])) {
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

    /**
     * Factory to create the provider.
     *
     * @static
     * @param ServiceLocatorInterface $sl
     * @param array                   $spec
     * @throws DomainException
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
}