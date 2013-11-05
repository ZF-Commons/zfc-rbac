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

namespace ZfcRbacTest\View\Helper;

use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use ZfcRbac\Options\UnauthorizedStrategyOptions;
use ZfcRbac\View\Strategy\UnauthorizedStrategy;

/**
 * @covers \ZfcRbac\View\Strategy\UnauthorizedStrategy
 */
class UnauthorizedStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testFillEvent()
    {
        $response = new HttpResponse();

        $mvcEvent = new MvcEvent();
        $mvcEvent->setResponse($response);
        $mvcEvent->setError('error');

        $options = new UnauthorizedStrategyOptions(array(
            'template'    => 'error/403',
            'status_code' => 403
        ));

        $unauthorizedStrategy = new UnauthorizedStrategy($options);

        $unauthorizedStrategy->onError($mvcEvent);

        $this->assertNotSame($response, $mvcEvent->getResponse(), 'Assert a new response is created');
        $this->assertEquals($options->getStatusCode(), $mvcEvent->getResponse()->getStatusCode());
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $mvcEvent->getResult());
        $this->assertEquals($options->getTemplate(), $mvcEvent->getResult()->getTemplate());
    }
}
 