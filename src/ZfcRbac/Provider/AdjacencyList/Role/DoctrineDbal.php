<?php

namespace ZfcRbac\Provider\AdjacencyList\Role;

use DomainException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
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
        $events->attach(Event::EVENT_LOAD_ROLES, array($this, 'loadRoles'));
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
     * Load roles at RBAC creation.
     *
     * @param Event $e
     * @return array
     */
    public function loadRoles(Event $e)
    {
        $builder = new QueryBuilder($this->connection);
        $options = $this->options;

        $builder->select("role.{$options->getNameColumn()} AS name, parent.{$options->getNameColumn()} AS parent")
                ->from($options->getTable(), 'role')
                ->leftJoin(
                    'role',
                    $options->getTable(),
                    'parent',
                    "role.{$options->getJoinColumn()} = parent.{$options->getIdColumn()}"
                );

        $result = $builder->execute();

        $roles = array();
        foreach($result as $row) {
            $parentName = isset($row['parent']) ? $row['parent'] : 0;
            unset($row['parent']);

            $roles[$parentName][] = $row['name'];
        }

        $this->recursiveRoles($e->getRbac(), $roles);
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