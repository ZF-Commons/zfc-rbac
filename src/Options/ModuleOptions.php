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

/**
 * Options for ZfcRbac module
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class ModuleOptions extends AbstractOptions
{
    /**
     * Guest role (used when no identity is found)
     *
     * @var string
     */
    protected $guestRole = 'guest';

    /**
     * Assertion map
     *
     * @var array
     */
    protected $assertionMap = [];

    /**
     * A configuration for role provider
     *
     * @var array
     */
    protected $roleProvider = [];

    /**
     * Constructor
     *
     * {@inheritDoc}
     */
    public function __construct($options = null)
    {
        $this->__strictMode__ = false;

        parent::__construct($options);
    }

    /**
     * Set the assertions options
     *
     * @param array $assertionMap
     * @return void
     */
    public function setAssertionMap(array $assertionMap)
    {
        $this->assertionMap = $assertionMap;
    }

    /**
     * Get the assertions options
     *
     * @return array
     */
    public function getAssertionMap()
    {
        return $this->assertionMap;
    }

    /**
     * Set the guest role (used when no identity is found)
     *
     * @param string $guestRole
     * @return void
     */
    public function setGuestRole($guestRole)
    {
        $this->guestRole = (string) $guestRole;
    }

    /**
     * Get the guest role (used when no identity is found)
     *
     * @return string
     */
    public function getGuestRole()
    {
        return $this->guestRole;
    }

    /**
     * Set the configuration for the role provider
     *
     * @param  array $roleProvider
     * @throws Exception\RuntimeException
     */
    public function setRoleProvider(array $roleProvider)
    {
        if (count($roleProvider) > 1) {
            throw new Exception\RuntimeException(
                'You can only have one role provider'
            );
        }

        $this->roleProvider = $roleProvider;
    }

    /**
     * Get the configuration for the role provider
     *
     * @return array
     */
    public function getRoleProvider()
    {
        return $this->roleProvider;
    }
}
