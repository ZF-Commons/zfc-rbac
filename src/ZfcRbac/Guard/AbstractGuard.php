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

namespace ZfcRbac\Guard;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use ZfcRbac\Exception;

/**
 * Abstract guard that registers on the "onRoute" event
 */
abstract class AbstractGuard implements GuardInterface, ListenerAggregateInterface
{
    /**
     * Various constants for guard that can be added to the MVC event result
     */
    const GUARD_AUTHORIZED    = 'guard-authorized';
    const GUARD_UNAUTHORIZED  = 'guard-unauthorized';
    const GUARD_RUNTIME_ERROR = 'guard-runtime-error';

    /**
     * Traits used
     */
    use ListenerAggregateTrait;

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'onRoute'));
    }

    /**
     * @param  MvcEvent $event
     * @return void
     */
    public function onRoute(MvcEvent $event)
    {
        try {
            if ($this->isGranted($event)) {
                $event->setParam('guard-result', self::GUARD_AUTHORIZED);
                return;
            } else {
                $event->setParam('guard-result', self::GUARD_UNAUTHORIZED);
            }
        } catch (Exception\RuntimeException $exception) {
            $event->setParam('guard-result', self::GUARD_RUNTIME_ERROR);
        }

        $application  = $event->getApplication();
        $eventManager = $application->getEventManager();

        $eventManager->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
    }
} 