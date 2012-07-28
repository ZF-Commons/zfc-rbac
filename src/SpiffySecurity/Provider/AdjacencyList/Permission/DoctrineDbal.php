<?php

namespace SpiffySecurity\Provider\AdjacencyList\Permission;

use DomainException;
use Doctrine\DBAL\Connection;
use SpiffySecurity\Provider\ProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DoctrineDbal implements ProviderInterface
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

        $builder->select("
                    p.{$options->getPermissionNameColumn()} AS permission,
                    r.{$options->getRoleNameColumn()} AS role
                ")
                ->from('permission', 'p')
                ->leftJoin(
                    'p',
                    'role_permission',
                    'rp',
                    "rp.{$options->getPermissionJoinColumn()} = p.{$options->getPermissionIdColumn()}"
                )->leftJoin(
                    'p',
                    'role',
                    'r',
                    "rp.{$options->getRoleJoinColumn()} = r.{$options->getRoleIdColumn()}"
                );

        $result = $builder->execute();
        $perms  = array();
        foreach($result as $row) {
            $perms[$row['role']][] = $row['permission'];
        }
        return $perms;
    }

    /**
     * Factory to create the provider.
     *
     * @static
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param mixed $spec
     * @return \SpiffySecurity\Provider\AdjacencyList\Permission\DoctrineDbal
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