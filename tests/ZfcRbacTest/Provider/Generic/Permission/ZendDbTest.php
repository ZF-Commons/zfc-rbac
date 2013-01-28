<?php

namespace ZfcRbacTest\Provider\Generic\Permission;

use ZfcRbac\Service\Rbac as RbacService;
use ZfcRbac\Provider\Generic\Permission\ZendDb;

use ZfcRbacTest\Bootstrap;
use ZfcRbacTest\RbacServiceTestCase;

class ZendDbTest extends RbacServiceTestCase
{
    // mock adapter
    protected $_mockAdapter;

    /**
    * @var Db
    */
    protected $_db;

    public function setUp()
    {
        $rbacService = new RbacService();
        self::setRbacService($rbacService);

        // replace service in service manager
        Bootstrap::getServiceManager()->setAllowOverride(true);
        Bootstrap::getServiceManager()->setService('ZfcRbac\Service\Rbac', $rbacService);

        // mock the adapter, driver, and parts
        $mockConnection = $this->getMock('Zend\Db\Adapter\Driver\ConnectionInterface');
        $this->_mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $this->_mockDriver->expects($this->any())->method('getConnection')->will($this->returnValue($mockConnection));
        $this->_mockAdapter = $this->getMock('Zend\Db\Adapter\Adapter', null, array($this->_mockDriver));

        // setUp Db
        $this->_db = new ZendDb($this->_mockAdapter, array());

        $rbac = $this->initRoles();
    }

    /**
     * Test exception when no role can be loaded
     * @return boolean
     */
    public function testNoPermissions()
    {
        // get RBAC service
        $rbacService = Bootstrap::getServiceManager()->get('ZfcRbac\Service\Rbac');
        // Event
        $e = new \ZfcRbac\Provider\Event();
        // assign Zend RBAC to event
        $e ->setRbac($rbacService->getRbac());

        $this->setExpectedException(
            'DomainException',
            'No permission loaded'
        );

        // mock statement returning an empty ResultSet
        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue(new \Zend\Db\ResultSet\ResultSet()));
        $this->_mockDriver->expects($this->once())->method('createStatement')->will($this->returnValue($mockStatement));

        $this->_db->loadPermissions($e);
    }

    /**
     * Test roles loded from Db
     * @return boolean
     */
    public function testPermissions()
    {
        // get RBAC service
        $rbacService = Bootstrap::getServiceManager()->get('ZfcRbac\Service\Rbac');
        // Event
        $e = new \ZfcRbac\Provider\Event();
        // assign Zend RBAC to event
        $e ->setRbac($rbacService->getRbac());

        // mock result
        $result = new \ArrayIterator(array(
            (object) array('permission'=>'admin', 'role'=>'parent'),
            (object) array('permission'=>'read', 'role'=>'child1'),
        ));

        // mock statement returning our roles
        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue($result));
        $this->_mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));

        // load roles with mock
        $this->_db->loadPermissions($e);

        // add provider to RBAC service
        $rbacService->addProvider($this->_db);
        $rbac = $rbacService->getRbac();

        // test roles
        $this->assertTrue($rbac->isGranted('parent', 'admin'));
        $this->assertTrue($rbac->isGranted('child1', 'read'));
        $this->assertTrue($rbac->isGranted('parent', 'read'));
        $this->assertFalse($rbac->isGranted('child1', 'admin'));
    }

}
