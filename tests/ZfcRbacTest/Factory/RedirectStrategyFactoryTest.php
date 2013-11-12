<?php
namespace ZfcRbacTest\Factory;

use ZfcRbac\Factory\RedirectStrategyFactory;

/**
 * @covers \ZfcRbac\Factory\RedirectStrategyFactory
 */
class RedirectStrategyFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $redirectStrategyOptions = $this->getMock('ZfcRbac\Options\RedirectStrategyOptions');

        $moduleOptionsMock = $this->getMock('ZfcRbac\Options\ModuleOptions');
        $moduleOptionsMock->expects($this->once())
                          ->method('getRedirectStrategy')
                          ->will($this->returnValue($redirectStrategyOptions));

        $serviceLocatorMock = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceLocatorMock->expects($this->once())
                           ->method('get')
                           ->with('ZfcRbac\Options\ModuleOptions')
                           ->will($this->returnValue($moduleOptionsMock));

        $factory          = new RedirectStrategyFactory();
        $redirectStrategy = $factory->createService($serviceLocatorMock);

        $this->assertInstanceOf('ZfcRbac\View\Strategy\RedirectStrategy', $redirectStrategy);
    }
}
 