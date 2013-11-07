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
use ZfcRbac\Exception;
use ZfcRbac\Guard\GuardInterface;

/**
 * Options for guards
 */
class GuardsOptions extends AbstractOptions
{
    /**
     * Protection policy (can be "deny" or "allow")
     *
     * @var string
     */
    protected $protectionPolicy = GuardInterface::POLICY_DENY;

    /**
     * Route rules
     *
     * @var array
     */
    protected $routeRules = array();

    /**
     * Controller rules
     *
     * @var array
     */
    protected $controllerRules = array();

    /**
     * Set the protection policy used by the guards
     *
     * @param  string $protectionPolicy
     * @return void
     * @throws Exception\RuntimeException
     */
    public function setProtectionPolicy($protectionPolicy)
    {
        if (!in_array($protectionPolicy, array(GuardInterface::POLICY_ALLOW, GuardInterface::POLICY_DENY))) {
            throw new Exception\RuntimeException(sprintf(
                'An invalid protection policy was set. Can only be "deny" or "allow", "%s" given',
                $protectionPolicy
            ));
        }

        $this->protectionPolicy = (string) $protectionPolicy;
    }

    /**
     * Get the protection policy
     *
     * @return string
     */
    public function getProtectionPolicy()
    {
        return $this->protectionPolicy;
    }

    /**
     * @param  array $routeRules
     * @return void
     */
    public function setRouteRules(array $routeRules)
    {
        $this->routeRules = $routeRules;
    }

    /**
     * @return array
     */
    public function getRouteRules()
    {
        return $this->routeRules;
    }

    /**
     * @param  array $controllerRules
     * @return void
     */
    public function setControllerRules(array $controllerRules)
    {
        $this->controllerRules = $controllerRules;
    }

    /**
     * @return array
     */
    public function getControllerRules()
    {
        return $this->controllerRules;
    }
}
