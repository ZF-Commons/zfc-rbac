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

namespace ZfcRbacTest\Asset;

use ZfcRbac\Assertion\AssertionInterface;
use ZfcRbac\Identity\IdentityInterface;

class SimpleAssertion implements AssertionInterface
{
    /**
     * @var int
     */
    protected $called = 0;

    /**
     * @var bool
     */
    protected $willAssert;

    public function __construct(bool $willAssert = true)
    {
        $this->willAssert = $willAssert;
    }

    public function assert(string $permission, IdentityInterface $identity = null, $context = null): bool
    {
        $this->called++;

        return $this->willAssert;
    }

    public function gotCalled(): bool
    {
        return (bool) $this->called;
    }

    public function calledTimes(): int
    {
        return $this->called;
    }
}
