<?php
/**
 * @package ZfcRbac
 * @subpackage Provider
 */
namespace ZfcRbac\Provider\AdjacencyList\Role;

use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\AdapterInterface;
use ZfcRbac\Provider\AbstractProvider;
use ZfcRbac\Provider\Event;
/**
 * Zend Db provider
 * @author jmleroux
 */
class ZendDb extends AbstractProvider
{
    /** @var AdapterInterface */
    protected $_adapter;

    /**
     * @var array
     */
    protected $roles;

    /**
     * @var CommonOptions
     */
    protected $_options;

    /**
     * @param Connection $connection
     * @param array $options
     */
    public function __construct(AdapterInterface $adapter, array $options)
    {
        $this->_adapter = $adapter;
        $this->_options  = new ZendDbOptions($options);
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
        $options = $this->_options;

        $sqlPattern = 'SELECT role.%s AS name, parent.%s AS parent
                FROM %s role
                LEFT JOIN %s parent
                ON role.%s = parent.%s';

        $values = array(
            $options->getNameColumn(),
            $options->getNameColumn(),
            $options->getTable(),
            $options->getTable(),
            $options->getJoinColumn(),
            $options->getIdColumn(),
        );

        $sql = vsprintf($sqlPattern, $values);

        $result = $this->_adapter->query($sql, array());

        if (!$result->count()) {
            throw new \DomainException('No role loaded');
        }

        $roles = array();
        foreach($result as $row) {
            if (isset($row->parent) && $row->parent) {
                $parentName = $row->parent;
            }
            else {
                $parentName = 0;
            }
            $roles[$parentName][] = $row->name;
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
     * @return Db
     */
    public static function factory(ServiceLocatorInterface $sl, array $spec)
    {
        $adapter = isset($spec['connection']) ? $spec['connection'] : null;
        if (!$adapter) {
            throw new \DomainException('Missing required parameter: connection');
        }

        if (!is_string($adapter) || $sl->has($adapter)) {
            $adapter = $sl->get($adapter);
        } else {
            throw new \DomainException('Failed to find Db Connection');
        }

        $options = isset($spec['options']) ? (array) $spec['options'] : array();

        return new ZendDb($adapter, array());
    }
}
