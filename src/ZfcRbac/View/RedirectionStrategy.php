<?php

namespace Rbac\View;

use ZfcRbac\Service\Rbac;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use ZfcRbac\Exception\AccessForbidden;
use Zend\Stdlib\ResponseInterface as Response;

class RedirectionStrategy implements ListenerAggregateInterface
{
    /**
     *  @var string route to be used to handle redirects
     */
    protected $redirectRoute = 'zfcuser/login';
    
    /**
     * @var string URI to be used to handle redirects
     */
    protected $redirectUri;

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Attach the aggregate to the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'prepareRedirectedViewModel'));
    }

    /**
     * Detach aggregate listeners from the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * @param string $redirectRoute
     */
    public function setRedirectRoute($redirectRoute)
    {
        $this->redirectRoute = (string) $redirectRoute;
    }
    
    /**
     * @param string|null $redirectUri
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri ? (string) $redirectUri : null;
    }

    /** 
     * Handles redirects in case of dispatch errors caused by unauthorized access
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function prepareRedirectedViewModel(MvcEvent $e)
    {
        // Do nothing if no error in the event
        $error = $e->getError();
        if (empty($error)) {
            return;
        }

        // early return if the result is a response object
        $result = $e->getResult();
        if ($result instanceof Response) {
            return;
        }
        
        $routeMatch = $e->getRouteMatch();
        $response = $e->getResponse();
        $router = $e->getRouter();
        
        $url = $this->redirectUri;
        
        if (
            !$routeMatch
            || ($response && ! $response instanceof Response)
            || ! (
                Rbac::ERROR_ROUTE_UNAUTHORIZED === $error
                || Rbac::ERROR_CONTROLLER_UNAUTHORIZED === $error
                || (
                    Application::ERROR_EXCEPTION === $error
                    && ($event->getParam('exception') instanceof AccessForbidden)
                )
            )
        ) {
            return;
        }
        
        if (null === $url) {
            $url = $router->assemble(array(), array('name' => $this->redirectRoute));
        }
        
        $response = $response ?: new HttpResponse();
        
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(302);
        $e->setResponse($response);
    }
}
