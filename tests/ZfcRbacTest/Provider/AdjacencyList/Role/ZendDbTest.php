<?php

namespace ZfcRbacTest\Provider\AdjacencyList\Role;

use ZfcRbac\Service\Rbac as RbacService;
use ZfcRbac\Provider\AdjacencyList\Role\ZendDb;

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

        Bootstrap::getServiceManager()->setAllowOverride(true);
        Bootstrap::getServiceManager()->setService('ZfcRbac\Service\Rbac', $rbacService);

        // mock the adapter, driver, and parts
        $mockConnection = $this->getMock('Zend\Db\Adapter\Driver\ConnectionInterface');
        $this->_mockDriver = $this->getMock('Zend\Db\Adapter\Driver\DriverInterface');
        $this->_mockDriver->expects($this->any())->method('getConnection')->will($this->returnValue($mockConnection));
        $this->_mockAdapter = $this->getMock('Zend\Db\Adapter\Adapter', null, array($this->_mockDriver));

        // set up Db
        $this->_db = new ZendDb($this->_mockAdapter, array());
    }

    /**
     * Test exception when no role can be loaded
     * @return boolean
     */
    public function testNoRoles()
    {
        $e = new \ZfcRbac\Provider\Event();
        $e ->setRbac(Bootstrap::getServiceManager()->get('ZfcRbac\Service\Rbac')->getRbac());

        $this->setExpectedException(
            'DomainException',
            'No role loaded'
        );

        // mock statement returning an empty ResultSet
        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue(new \Zend\Db\ResultSet\ResultSet()));
        $this->_mockDriver->expects($this->once())->method('createStatement')->will($this->returnValue($mockStatement));

        // load roles with mock
        // exception expected
        $this->_db->loadRoles($e);
    }

    /**
     * Test roles loded from Db
     * @return boolean
     */
    public function testRoles()
    {
        // get RBAC service
        $rbacService = Bootstrap::getServiceManager()->get('ZfcRbac\Service\Rbac');
        // Event
        $e = new \ZfcRbac\Provider\Event();
        // assign Zend RBAC to event
        $e ->setRbac($rbacService->getRbac());

        // mock result
        $result = new \ArrayIterator(array(
            (object) array('name'=>'parent', 'parent'=>null),
            (object) array('name'=>'child1', 'parent'=>'parent'),
            (object) array('name'=>'child2', 'parent'=>'parent'),
            (object) array('name'=>'subchild', 'parent'=>'child1'),
        ));

        // mock statement returning our roles
        $mockStatement = $this->getMock('Zend\Db\Adapter\Driver\StatementInterface');
        $mockStatement->expects($this->any())->method('execute')->will($this->returnValue($result));
        $this->_mockDriver->expects($this->any())->method('createStatement')->will($this->returnValue($mockStatement));

        // load roles with mock
        $this->_db->loadRoles($e);

        // add provider to RBAC service
        $rbacService->addProvider($this->_db);
        $rbac = $rbacService->getRbac();

        // test roles
        $this->assertTrue($rbac->hasRole('parent'));
        $this->assertTrue($rbac->hasRole('child1'));
        $this->assertTrue($rbac->hasRole('child2'));
        $this->assertTrue($rbac->hasRole('subchild'));
        $this->assertFalse($rbac->hasRole('child3'));
    }

}
