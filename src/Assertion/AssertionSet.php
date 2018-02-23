<?php

declare(strict_types=1);

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

namespace ZfcRbac\Assertion;

use ZfcRbac\Exception;
use ZfcRbac\Identity\IdentityInterface;

final class AssertionSet implements AssertionInterface
{
    /**
     * Condition constants
     */
    const CONDITION_OR = 'condition_or';
    const CONDITION_AND = 'condition_and';

    /**
     * @var AssertionContainerInterface
     */
    private $assertionContainer;

    /**
     * @var array
     */
    private $assertions = [];

    private $condition = self::CONDITION_AND;

    public function __construct(AssertionContainerInterface $assertionContainer, array $assertions)
    {
        if (isset($assertions['condition'])) {
            if ($assertions['condition'] !== AssertionSet::CONDITION_AND
                && $assertions['condition'] !== AssertionSet::CONDITION_OR) {
                throw new Exception\InvalidArgumentException('Invalid assertion condition given.');
            }

            $this->condition = $assertions['condition'];

            unset($assertions['condition']);
        }

        $this->assertions = $assertions;
        $this->assertionContainer = $assertionContainer;
    }

    public function assert(string $permission, IdentityInterface $identity = null, $context = null): bool
    {
        if (empty($this->assertions)) {
            return false;
        }

        $assertedCount = 0;

        foreach ($this->assertions as $index => $assertion) {
            switch (true) {
                case is_callable($assertion):
                    $asserted = $assertion($permission, $identity, $context);
                    break;
                case $assertion instanceof AssertionInterface:
                    $asserted = $assertion->assert($permission, $identity, $context);
                    break;
                case is_string($assertion):
                    $this->assertions[$index] = $assertion = $this->assertionContainer->get($assertion);

                    $asserted = $assertion->assert($permission, $identity, $context);
                    break;
                case is_array($assertion):
                    $this->assertions[$index] = $assertion = new AssertionSet($this->assertionContainer, $assertion);
                    $asserted = $assertion->assert($permission, $identity, $context);
                    break;
                default:
                    throw new Exception\InvalidArgumentException(sprintf(
                        'Assertion must be callable, string, array or implement ZfcRbac\Assertion\AssertionInterface, "%s" given',
                        is_object($assertion) ? get_class($assertion) : gettype($assertion)
                    ));
            }

            switch ($this->condition) {
                case AssertionSet::CONDITION_AND:
                    if (false === $asserted) {
                        return false;
                    }

                    break;
                case AssertionSet::CONDITION_OR:
                    if (true === $asserted) {
                        return true;
                    }
                    break;
            }

            $assertedCount++;
        }

        if (AssertionSet::CONDITION_AND === $this->condition && count($this->assertions) === $assertedCount) {
            return true;
        }

        return false;
    }
}
