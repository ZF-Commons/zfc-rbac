<?php

namespace SpiffySecurity\Provider\AdjacencyList\Role;

use DomainException;
use Doctrine\DBAL\Connection;
use SpiffySecurity\Provider\AbstractProvider;
use SpiffySecurity\Provider\Event;
use SpiffySecurity\Provider\ProviderInterface;
use SpiffySecurity\Rbac\Rbac;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class DoctrineDbal extends AbstractProvider implements ProviderInterface
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
     * @param \Zend\EventManager\EventManager $events
     * @return void
     */
    public function attachListeners(EventManager $events)
    {
        $events->attach(Event::EVENT_ON_LOAD, array($this, 'onLoad'));
    }

    /**
     * Load roles at RBAC creation.
     *
     * @param Event $e
     * @return array
     */
    public function onLoad(Event $e)
    {
        $builder = new \Doctrine\DBAL\Query\QueryBuilder($this->connection);
        $options = $this->options;

        $builder->select('(COUNT(parent.name)-1) AS depth, node.name')
            ->from($options->getTable(), 'node')
            ->from($options->getTable(), 'parent')
            ->where('node.lft BETWEEN parent.lft AND parent.rgt')
            ->groupBy('node.name')
            ->orderBy('node.lft');

        $result = $builder->execute();
        $roles  = array();
        $last   = null;
        foreach($result as $row) {
            if ($row['depth'] == 0) {
                $last   = null;
                $parent = 0;
            } else {
                $parent = $last;
            }

            $last = $roles[$parent][] = $row['name'];
        }

        $this->recursiveRoles($e->getRbac(), $roles);
    }

    /**
     * Factory to create the provider.
     *
     * @static
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param mixed $spec
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