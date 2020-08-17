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
class AssertionSet implements AssertionInterface
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
    protected $condition = AssertionSet::CONDITION_AND;

    /**
     * Constructor.
     *
     * @param array|AssertionInterface[] $assertions An array of assertions.
     */
    public function __construct(array $assertions = array())
    {
        $this->assertions = [];

        // if definition contains condition, set it.
        if (isset($assertions['condition'])) {
            if ($assertions['condition'] != AssertionSet::CONDITION_AND
                && $assertions['condition'] != AssertionSet::CONDITION_OR) {
                throw new InvalidArgumentException('Invalid assertion condition given.');
            }
            $this->condition = $assertions['condition'];
        }
        // if there are multiple assertions under a key 'assertions', get them.
        if (isset($assertions['assertions']) && is_array($assertions['assertions'])) {
            $assertions = $assertions['assertions'];
        }
        // set each assertion
        foreach ($assertions as $name => $assertion) {
            if (is_int($name)) {
                $this->assertions[] = $assertion;
            } else {
                $this->assertions[$name] = $assertion;
            }
        }
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
        if (empty($this->assertions)) {
            return true;
        }

        if (AssertionSet::CONDITION_AND === $this->condition) {
            foreach ($this->assertions as $assertion) {
                if (!$assertion->assert($authorizationService, $context)) {
                    return false;
                }
            }

            return true;
        }

        if (AssertionSet::CONDITION_OR === $this->condition) {
            foreach ($this->assertions as $assertion) {
                if ($assertion->assert($authorizationService, $context)) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }
}
