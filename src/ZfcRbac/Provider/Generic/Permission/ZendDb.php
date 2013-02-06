<?php
/**
 * @package ZfcRbac
 * @subpackage Provider
 */
namespace ZfcRbac\Provider\Generic\Permission;

use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcRbac\Provider\AbstractProvider;
use ZfcRbac\Provider\Event;
use Zend\Db\Adapter\AdapterInterface;

class ZendDb extends AbstractProvider
{
    /**
     * @var AdapterInterface
     */
    protected $_adapter;

    /**
     * @var array
     */
    protected $_roles;

    /**
     * @var Options
     */
    protected $_options;

    /**
     * @param AdapterInterface $connection
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
        $options = $this->_options;

        $sqlPattern = 'SELECT p.%s AS permission, r.%s AS role
            FROM %s p
            LEFT JOIN %s rp ON rp.%s = p.%s
            LEFT JOIN %s r ON rp.%s = r.%s';

        $values = array(
            $options->getPermissionNameColumn(),
            $options->getRoleNameColumn(),
            $options->getPermissionTable(),
            $options->getRoleJoinTable(),
            $options->getPermissionJoinColumn(),
            $options->getPermissionIdColumn(),
            $options->getRoleTable(),
            $options->getRoleJoinColumn(),
            $options->getRoleIdColumn(),
        );

        $sql = vsprintf($sqlPattern, $values);

        $result = $this->_adapter->query($sql, array());

        if (!$result->count()) {
            throw new \DomainException('No permission loaded');
        }

        $rbac    = $e->getRbac();

        foreach($result as $row) {
            if ($rbac->hasRole($row->role)) {
                $rbac->getRole($row->role)->addPermission($row->permission);
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
