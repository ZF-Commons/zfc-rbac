<?php

namespace SpiffySecurity\Provider;

use DomainException;
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
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @param array $options
     */
    public function __construct(Adapter $adapter, array $options)
    {
        $this->adapter = $adapter;
        $this->options = new ZendDbOptions($options);
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        if (null === $this->roles) {
            $this->load();
        }
        return $this->roles;
    }

    /**
     * Load the roles from db.
     */
    protected function load()
    {
        $options = $this->options;
        $sql     = new Sql($this->adapter);

        $select = $sql->select();
        $select->columns(array('role' => $options->getNameColumn()));
        $select->from(array('role' => $options->getTable()));

        $stmt    = $sql->prepareStatementForSqlObject($select);
        $results = $stmt->execute();

        $roles = array();
        foreach($results as $row) {
            $roles[] = $row['role'];
        }
        $this->roles = $roles;
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