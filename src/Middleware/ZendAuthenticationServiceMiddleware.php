<?php

namespace ZfcRbac\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Stratigility\MiddlewareInterface;

class ZendAuthenticationServiceMiddleware implements MiddlewareInterface
{
    /**
     * @var AuthenticationService
     */
    private $authService;

    /**
     * @var string
     */
    private $attributeName;

    /**
     * ZendAuthenticationServiceMiddleware constructor.
     *
     * @param AuthenticationService $authService
     * @param string                $attributeName
     */
    public function __construct(AuthenticationService $authService, $attributeName = 'identity')
    {
        $this->authService   = $authService;
        $this->attributeName = $attributeName;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable|null          $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        if ($request->getAttribute($this->attributeName) === null && $this->authService->hasIdentity()) {
            $identity = $this->authService->getIdentity();
            $request  = $request->withAttribute($this->attributeName, $identity);
        }

        if ($next) {
            $response = $next($request, $response);
        }

        return $response;
    }
}
