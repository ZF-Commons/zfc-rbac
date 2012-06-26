<?php

namespace SpiffySecurity\Provider\Role;

use DomainException;
use Doctrine\DBAL\Connection;
use SpiffySecurity\Rbac\Rbac;
use Zend\ServiceManager\ServiceLocatorInterface;

class DoctrineDBAL implements RoleInterface
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
     * @var DoctrineDBALOptions
     */
    protected $options;

    /**
     * @param Connection $connection
     * @param array $options
     */
    public function __construct(connection $connection, array $options)
    {
        $this->connection = $connection;
        $this->options    = new DoctrineDBALOptions($options);
    }

    /**
     * Load permissions into roles.
     *
     * @abstract
     * @param Rbac $rbac
     * @return mixed
     */
    public function load(Rbac $rbac)
    {
        $builder = new \Doctrine\DBAL\Query\QueryBuilder($this->connection);
        $options = $this->options;

        if ($options->getJoinColumn()) {
            $builder->select("role.{$options->getNameColumn()} AS name, parent.{$options->getNameColumn()} AS parent")
                    ->from('role', 'role')
                    ->leftJoin('role', 'role', 'parent', "role.{$options->getJoinColumn()} = parent.{$options->getIdColumn()}");
        } else {
            // todo: implement non-joined query
        }

        $result = $builder->execute();

        $roles = array();
        foreach($result as $row) {
            $parentName = isset($row['parent']) ? $row['parent'] : 0;
            unset($row['parent']);

            $roles[$parentName][] = $row['name'];
        }
        return $roles;
    }

    /**
     * Factory to create the provider.
     *
     * @static
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param mixed $spec
     * @return \SpiffySecurity\Provider\Role\DoctrineDBAL
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

        return new \SpiffySecurity\Provider\Role\DoctrineDBAL($adapter, $options);
    }
}