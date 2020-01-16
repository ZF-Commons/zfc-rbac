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

declare(strict_types=1);

namespace ZfcRbac\Options;

use Laminas\Stdlib\AbstractOptions;

/**
 * Options for ZfcRbac module
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
final class ModuleOptions extends AbstractOptions
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
     * {@inheritdoc}
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
    public function setAssertionMap(array $assertionMap): void
    {
        $this->assertionMap = $assertionMap;
    }

    /**
     * Get the assertions options
     *
     * @return array
     */
    public function getAssertionMap(): array
    {
        return $this->assertionMap;
    }

    /**
     * Set the guest role (used when no identity is found)
     *
     * @param string $guestRole
     * @return void
     */
    public function setGuestRole(string $guestRole): void
    {
        $this->guestRole = $guestRole;
    }

    /**
     * Get the guest role (used when no identity is found)
     *
     * @return string
     */
    public function getGuestRole(): string
    {
        return $this->guestRole;
    }

    /**
     * Set the configuration for the role provider
     *
     * @param array $roleProvider
     */
    public function setRoleProvider(array $roleProvider): void
    {
        $this->roleProvider = $roleProvider;
    }

    public function getRoleProvider(): array
    {
        return $this->roleProvider;
    }
}
