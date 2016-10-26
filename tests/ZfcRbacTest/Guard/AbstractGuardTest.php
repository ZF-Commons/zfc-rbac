<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ZfcRbacTest\Guard;

use Zend\EventManager\EventManager;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use ZfcRbacTest\Asset\DummyGuard;

/**
 * @covers \ZfcRbac\Guard\AbstractGuard
 * @covers \ZfcRbac\Guard\ControllerGuard
 */
class AbstractGuardTest extends \PHPUnit_Framework_TestCase
{
    public function testDoesNotLimitDispatchErrorEventToOnlyOneListener()
    {
        $eventManager = new EventManager();
        $application = $this->prophesize(Application::class);
        $application->getEventManager()->willReturn($eventManager);

        $event = new MvcEvent();
        $event->setApplication($application->reveal());

        $guard = new DummyGuard();
        $guard->attach($eventManager);

        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function (MvcEvent $event) {
            $event->setParam('first-listener', true);
        });
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function (MvcEvent $event) {
            $event->setParam('second-listener', true);
        });

        // attach listener with lower priority than DummyGuard
        $eventManager->attach(MvcEvent::EVENT_ROUTE, function (MvcEvent $event) {
            $this->fail('should not be called, because guard should stop propagation');
        }, DummyGuard::EVENT_PRIORITY - 1);

        $event->setName(MvcEvent::EVENT_ROUTE);
        $eventManager->triggerEvent($event);

        $this->assertTrue($event->getParam('first-listener'));
        $this->assertTrue($event->getParam('second-listener'));
        $this->assertTrue($event->propagationIsStopped());
    }
}
