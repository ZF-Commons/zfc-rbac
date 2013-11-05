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

namespace ZfcRbac\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Redirect strategy options
 */
class RedirectStrategyOptions extends AbstractOptions
{
    /**
     * The name of the route to redirect when a user is not authorized
     *
     * @var string
     */
    protected $redirectToRoute;

    /**
     * Should the previous URI should be appended as a query param?
     *
     * @var bool
     */
    protected $appendPreviousUri = true;

    /**
     * If appendPreviousUri is enabled, key to use in query params that hold the previous URI
     *
     * @var string
     */
    protected $previousUriQueryKey = 'redirectTo';

    /**
     * @param string $redirectToRoute
     */
    public function setRedirectToRoute($redirectToRoute)
    {
        $this->redirectToRoute = (string) $redirectToRoute;
    }

    /**
     * @return string
     */
    public function getRedirectToRoute()
    {
        return $this->redirectToRoute;
    }

    /**
     * @param boolean $appendPreviousUri
     */
    public function setAppendPreviousUri($appendPreviousUri)
    {
        $this->appendPreviousUri = (bool) $appendPreviousUri;
    }

    /**
     * @return boolean
     */
    public function getAppendPreviousUri()
    {
        return $this->appendPreviousUri;
    }

    /**
     * @param string $previousUriQueryKey
     */
    public function setPreviousUriQueryKey($previousUriQueryKey)
    {
        $this->previousUriQueryKey = (string) $previousUriQueryKey;
    }

    /**
     * @return string
     */
    public function getPreviousUriQueryKey()
    {
        return $this->previousUriQueryKey;
    }
} 