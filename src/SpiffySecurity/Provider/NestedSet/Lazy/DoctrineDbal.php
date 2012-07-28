<?php

namespace SpiffySecurity\Provider\NestedSet\Lazy;

use DomainException;
use Doctrine\DBAL\Connection;
use SpiffySecurity\Provider\Event;
use SpiffySecurity\Provider\ProviderInterface;
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
        $events->attach(Event::EVENT_IS_GRANTED, array($this, 'isGranted'));
    }

    /**
     * Load permissions into roles.
     *
     * @abstract
     * @param Event $e
     * @return mixed
     */
    public function isGranted(Event $e)
    {
        $granted    = false;
        $rbac       = $e->getRbac();
        $role       = $e->getRole();
        $permission = $e->getPermission();
        $builder    = new \Doctrine\DBAL\Query\QueryBuilder($this->connection);
        $options    = $this->options;

        if ($rbac->hasRole($role) && $rbac->getRole($role)->hasPermission($permission)) {
            return true;
        }

        $builder->select('node.name')
                ->from($options->getRoleTable(), 'node')
                ->from($options->getRoleTable(), 'parent')
                ->where('node.lft BETWEEN parent.lft AND parent.rgt')
                ->andWhere('parent.name = :role')
                ->orderBy('node.lft');

        $builder->addSelect('permission.name AS permission')
                ->leftJoin('node', 'role_permission', 'rp', 'node.id = rp.role_id')
                ->leftJoin('node', 'permission', 'permission', 'rp.permission_id = permission.id')
                ->andWhere('(permission.name = :permission OR permission.name IS NULL)');

        $builder->setParameter('permission', $permission);
        $builder->setParameter('role', $role);

        $parent = null;
        foreach($builder->execute() as $row) {
            if ($parent) {
                if (!$rbac->hasRole($row['name'])) {
                    $rbac->getRole($parent)->addChild($row['name']);
                }
            } else if (!$rbac->hasRole($row['name'])) {
                $rbac->addRole($row['name']);
            }

            if ($row['permission']) {
                $rbac->getRole($row['name'])->addPermission($row['permission']);
            }

            $parent = $row['name'];
        }

        return $rbac->getRole($role)->hasPermission($permission);
    }

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
}