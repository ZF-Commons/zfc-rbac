<?php

namespace SpiffySecurity\Provider;

use DomainException;
use SpiffySecurity\Rbac\Rbac;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\ServiceLocatorInterface;

class ZendDb implements ProviderInterface
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
     * @var \SpiffySecurity\Provider\ZendDbOptions
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

    public function load(Rbac $rbac)
    {
        $options = $this->options;
        $sql     = new Sql($this->adapter);

        $select = $sql->select();

        if ($options->getJoinColumn()) {
            $select->columns(array(
                'id'   => 'id',
                'name' => $options->getNameColumn(),
            ));
            $select->from(array('role' => $options->getTable()));
            $select->join(
                array('parent' => $options->getTable()),
                "role.{$options->getJoinColumn()} = parent.{$options->getIdColumn()}",
                array('parent_id' => 'id'),
                $select::JOIN_LEFT
            );
        } else {
            // todo: implement non-joined query
        }

        $stmt    = $sql->prepareStatementForSqlObject($select);
        $results = $stmt->execute();

        $roles = array();
        foreach($results as $row) {
            if (empty($row['parent_id'])) {
                $row['parent_id'] = 0;
            }
            $roles[$row['parent_id']][] = $row;
        }

        ksort($roles);
        $this->loadRbac($rbac, $roles);
    }

    protected function loadRbac(Rbac $rbac, $roles, $parentId = 0, $parentName = null)
    {
        foreach ($roles[$parentId] as $role) {
            if ($parentName) {
                $rbac->getChild($parentName)->addChild(new \SpiffySecurity\Rbac\Role($role['name']));
            } else {
                $rbac->addChild($role['name']);
            }
            if (!empty($roles[$role['id']])) {
                $this->loadRbac($rbac, $roles, $role['id'], $role['name']);
            }
        }
    }

    /**
     * Factory to create the provider.
     *
     * @static
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param mixed $spec
     * @return \SpiffySecurity\Provider\ZendDb
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

        return new \SpiffySecurity\Provider\ZendDb($adapter, $options);
    }
}