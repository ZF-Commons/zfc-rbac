<?php
namespace ZfcRbacTest\Factory;

use ZfcRbac\Factory\UnauthorizedStrategyFactory;

/**
 * @covers \ZfcRbac\Factory\UnauthorizedStrategyFactory
 */
class UnauthorizedStrategyFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $unauthorizedStrategyOptions = $this->getMock('ZfcRbac\Options\UnauthorizedStrategyOptions');

        $moduleOptionsMock = $this->getMock('ZfcRbac\Options\ModuleOptions');
        $moduleOptionsMock->expects($this->once())
                          ->method('getUnauthorizedStrategy')
                          ->will($this->returnValue($unauthorizedStrategyOptions));

        $serviceLocatorMock = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceLocatorMock->expects($this->once())
                           ->method('get')
                           ->with('ZfcRbac\Options\ModuleOptions')
                           ->will($this->returnValue($moduleOptionsMock));

        $factory              = new UnauthorizedStrategyFactory();
        $unauthorizedStrategy = $factory->createService($serviceLocatorMock);

        $this->assertInstanceOf('ZfcRbac\View\Strategy\UnauthorizedStrategy', $unauthorizedStrategy);
    }
}
 