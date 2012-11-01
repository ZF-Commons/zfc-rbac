<?php

namespace ZfcRbac\Provider\Generic\Permission;

use DomainException;
use Doctrine\DBAL\Connection;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcRbac\Provider\AbstractProvider;
use ZfcRbac\Provider\Event;

class DoctrineDbal extends AbstractProvider
{
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
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $events->attach(Event::EVENT_LOAD_PERMISSIONS, array($this, 'loadPermissions'));
    }

    /**
     * @param EventManagerInterface $events
     */
    public function detach(EventManagerInterface $events)
    {
        $events->detach($this);
    }

    /**
     * Load permissions into roles.
     *
     * @param Event $e
     */
    public function loadPermissions(Event $e)
    {
        $rbac    = $e->getRbac();
        $builder = new \Doctrine\DBAL\Query\QueryBuilder($this->connection);
        $options = $this->options;
		$builder->select("
                    p.{$options->getPermissionNameColumn()} AS permission,
                    r.{$options->getRoleNameColumn()} AS role
                ")
                ->from($options->getPermissionTable(), 'p')
                ->leftJoin(
                    'p',
                    $options->getRoleJoinTable(),
                    'rp',
                    "rp.{$options->getPermissionJoinColumn()} = p.{$options->getPermissionIdColumn()}"
                )->leftJoin(
                    'p',
                    $options->getRoleTable(),
                    'r',
                    "rp.{$options->getRoleJoinColumn()} = r.{$options->getRoleIdColumn()}"
                );

        foreach($builder->execute() as $row) {
            if ($rbac->hasRole($row['role'])) {
                $rbac->getRole($row['role'])->addPermission($row['permission']);
            }
        }
    }

    /**
     * Factory to create the provider.
     *
     * @static
     * @param ServiceLocatorInterface $sl
     * @param array                   $spec
     * @throws DomainException
     * @return DoctrineDbal
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