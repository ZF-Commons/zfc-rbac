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
namespace ZfcRbac\Assertion;

use ZfcRbac\Exception\InvalidArgumentException;
use ZfcRbac\Service\AuthorizationService;

/**
 * Assertion set to hold and process multiple assertions
 *
 * @author  David Havl
 * @licence MIT
 */

class AssertionSet implements AssertionInterface, \IteratorAggregate
{
    /**
     * Condition constants
     */
    const CONDITION_OR  = 'OR';
    const CONDITION_AND = 'AND';

    /**
     * @var $assertions array
     */
    protected $assertions = [];

    /**
     * @var $condition string
     */
    protected $condition = 'AND';

    /**
     * Constructor.
     *
     * @param array|AssertionInterface[] $assertions An array of assertions.
     */
    public function __construct(array $assertions = array())
    {
        $this->setAssertions($assertions);
    }

    /**
     * Set assertions.
     *
     * @param AssertionInterface[] $assertions The assertions to set
     *
     * @return $this
     */
    public function setAssertions(array $assertions)
    {
        $this->assertions = [];

        // if definition contains condition, set it.
        if (isset($assertions['condition'])) {
            if ($assertions['condition'] != self::CONDITION_AND && $assertions['condition'] != self::CONDITION_OR) {
                throw new InvalidArgumentException('Invalid assertion condition given.');
            }
            $this->setCondition($assertions['condition']);
        }

        // if there are multiple assertions under a key 'assertions', get them.
        if (isset($assertions['assertions']) && is_array($assertions['assertions'])) {
            $assertions = $assertions['assertions'];
        }

        // set each assertion
        foreach ($assertions as $name => $assertion) {
            $this->setAssertion($assertion, is_int($name) ? null : $name);
        }
        return $this;
    }

    /**
     * Set an assertion.
     *
     * @param string|AssertionInterface $assertion The assertion instance or it's name
     * @param string $name A name/alias
     * @return $this
     */
    public function setAssertion(AssertionInterface $assertion, $name = null)
    {
        if (null !== $name) {
            $this->assertions[$name] = $assertion;
        } else {
            $this->assertions[] = $assertion;
        }
    }

    /**
     * Returns true if the assertion if defined.
     *
     * @param string $name The assertion name
     *
     * @return bool true if the assertion is defined, false otherwise
     */
    public function hasAssertion($name)
    {
        return isset($this->assertions[$name]);
    }

    /**
     * Gets a assertion value.
     *
     * @param string $name The assertion name
     *
     * @return AssertionInterface The assertion instance
     *
     * @throws InvalidArgumentException if the assertion is not defined
     */
    public function getAssertion($name)
    {
        if (!$this->hasAssertion($name)) {
            throw new InvalidArgumentException(sprintf('The assertion "%s" is not defined.', $name));
        }
        return $this->assertions[$name];
    }

    /**
     * Gets all assertions.
     *
     * @return AssertionInterface[] The assertion instance
     */
    public function getAssertions()
    {
        return $this->assertions;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Set condition
     *
     * @param string $condition
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    /**
     * Retrieve an external iterator
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->assertions);
    }

    /**
     * Check if assertions are successful
     *
     * @param  AuthorizationService $authorizationService
     * @param  mixed                $context
     * @return bool
     */
    public function assert(AuthorizationService $authorizationService, $context = null)
    {
        if (self::CONDITION_AND === $this->condition) {
            foreach ($this->assertions as $assertion) {
                if (!$assertion->assert($authorizationService, $context)) {
                    return false;
                }
            }

            return true;
        }

        if (self::CONDITION_OR === $this->condition) {
            foreach ($this->assertions as $assertion) {
                if ($assertion->assert($authorizationService, $context)) {
                    return true;
                }
            }

            return false;
        }

    }
}
