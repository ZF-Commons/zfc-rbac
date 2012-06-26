<?php

namespace SpiffySecurity\Provider\Role;

use DomainException;
use SpiffySecurity\Rbac\Rbac;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\ServiceLocatorInterface;

class ZendDb implements RoleInterface
{
    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $roles;

    /**
     * @var \SpiffySecurity\Provider\Role\ZendDbOptions
     */
    protected $options;

    /**
     * @param Adapter $adapter
     * @param array $options
     */
    public function __construct(Adapter $adapter, array $options)
    {
        $this->adapter = $adapter;
        $this->options = new ZendDbOptions($options);
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
        $options = $this->options;
        $sql     = new Sql($this->adapter);

        $select = $sql->select();

        if ($options->getJoinColumn()) {
            $select->columns(array(
                'name' => $options->getNameColumn(),
            ));
            $select->from(array('role' => $options->getTable()));
            $select->join(
                array('parent' => $options->getTable()),
                "role.{$options->getJoinColumn()} = parent.{$options->getIdColumn()}",
                array('parent' => 'name'),
                $select::JOIN_LEFT
            );
        } else {
            // todo: implement non-joined query
        }

        $stmt    = $sql->prepareStatementForSqlObject($select);
        $results = $stmt->execute();

        $roles = array();
        foreach($results as $row) {
            $parentName = isset($row['parent']) ? $row['parent'] : 0;
            unset($row['parent']);

            $roles[$parentName][] = $row['name'];
        }
        $this->loadRbac($rbac, $roles);
    }

    /**
     * Factory to create the provider.
     *
     * @static
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param mixed $spec
     * @return \SpiffySecurity\Provider\Role\ZendDb
     */
    public static function factory(ServiceLocatorInterface $sl, array $spec)
    {
        $adapter = isset($spec['adapter']) ? $spec['adapter'] : null;
        if (!$adapter) {
            throw new DomainException('Missing required parameter: adapter');
        }

        $options = isset($spec['options']) ? (array) $spec['options'] : array();
        if (is_string($adapter) && $sl->has($adapter)) {
            $adapter = $sl->get($adapter);
        } else if (is_array($adapter)) {
            $adapter = new Adapter($adapter);
        } else {
            throw new DomainException('Adapter should be a service locator alias or an array');
        }

        return new \SpiffySecurity\Provider\Role\ZendDb($adapter, $options);
    }
}