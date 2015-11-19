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

namespace ZfcRbacTest\Middleware;

use Zend\Authentication\AuthenticationService;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use ZfcRbac\Middleware\ZendAuthenticationServiceMiddleware;

/**
 * Expected behavior of any Identity Providing Middleware
 *
 * Principle task : retrieves an identity from an arbitrary source and stores it as an attribute on the request so that
 * middleware further down the chain can use it.
 *
 * Following these rules :
 *  - When an identity attribute is already present on the request do *not* override it.
 *  - When an identity is not present from its source
 *
 * @covers \ZfcRbac\Middleware\ZendAuthenticationServiceMiddleware
 */
class ZendAuthenticationServiceMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    public function testProviderHasIdentityAndRequestDoesNotHaveIdentityAttribute()
    {
        $identity      = 'mrx';
        $attributeName = 'identity';
        $request       = new ServerRequest();
        $response      = new Response();

        $authServiceMock = $this->getMock(AuthenticationService::class);
        $authServiceMock->expects($this->once())->method('hasIdentity')->will($this->returnValue(true));
        $authServiceMock->expects($this->once())->method('getIdentity')->will($this->returnValue($identity));

        $next = function ($request, $response, $error = false) use ($identity, $attributeName) {
            $this->assertEquals($identity, $request->getAttribute($attributeName));

            return $response;
        };

        $middleware = new ZendAuthenticationServiceMiddleware($authServiceMock, $attributeName);
        $middleware($request, $response, $next);
    }

    public function testProviderHasIdentityAndRequestHasIdentityAttribute()
    {
        $identity      = 'mrx';
        $attributeName = 'identity';
        $request       = new ServerRequest();
        $request       = $request->withAttribute($attributeName, $identity);
        $response      = new Response();

        $authServiceMock = $this->getMock(AuthenticationService::class);
        $authServiceMock->expects($this->never())->method('hasIdentity');
        $authServiceMock->expects($this->never())->method('getIdentity');

        $next = function ($request, $response, $error = false) use ($identity, $attributeName) {
            $this->assertEquals($identity, $request->getAttribute($attributeName));

            return $response;
        };

        $middleware = new ZendAuthenticationServiceMiddleware($authServiceMock, $attributeName);
        $middleware($request, $response, $next);
    }

    public function testProviderDoesNotHaveIdentityAndRequestHasIdentityAttribute()
    {
        $identity      = 'mrx';
        $attributeName = 'identity';
        $request       = new ServerRequest();
        $request       = $request->withAttribute($attributeName, $identity);
        $response      = new Response();

        $authServiceMock = $this->getMock(AuthenticationService::class);
        $authServiceMock->expects($this->never())->method('getIdentity');
        $authServiceMock->expects($this->never())->method('getIdentity');

        $next = function ($request, $response, $error = false) use ($identity, $attributeName) {
            $this->assertEquals($identity, $request->getAttribute($attributeName));

            return $response;
        };

        $middleware = new ZendAuthenticationServiceMiddleware($authServiceMock, $attributeName);
        $middleware($request, $response, $next);
    }

    public function testProviderDoesNotHaveIdentityAndRequestDoesNotHaveIdentityAttribute()
    {
        $identity      = null;
        $attributeName = 'identity';
        $request       = new ServerRequest();
        $request       = $request->withAttribute($attributeName, $identity);
        $response      = new Response();

        $authServiceMock = $this->getMock(AuthenticationService::class);
        $authServiceMock->expects($this->once())->method('hasIdentity')->will($this->returnValue(false));
        $authServiceMock->expects($this->never())->method('getIdentity');

        $next = function ($request, $response, $error = false) use ($identity, $attributeName) {
            $this->assertEquals($identity, $request->getAttribute($attributeName));

            return $response;
        };

        $middleware = new ZendAuthenticationServiceMiddleware($authServiceMock, $attributeName);
        $middleware($request, $response, $next);
    }
}
