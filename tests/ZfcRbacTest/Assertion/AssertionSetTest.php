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

namespace ZfcRbacTest;

use ZfcRbac\Assertion\AssertionSet;
use ZfcRbac\Options\ModuleOptions;
use ZfcRbacTest\Asset\SimpleFalseAssertion;
use ZfcRbacTest\Asset\SimpleTrueAssertion;
use ZfcRbacTest\Util\ServiceManagerFactory;

/**
 * @covers \ZfcRbac\Assertion\AssertionSet
 */
class AssertionSetTest extends \PHPUnit_Framework_TestCase
{
    public function testAssertDefaultOptions()
    {
        /** @var \ZfcRbac\Assertion\AssertionSet $assertionSet */
        $assertionSet = new AssertionSet();

        $this->assertEquals('AND', $assertionSet->getCondition());
        $this->assertEquals([], $assertionSet->getAssertions());
    }

    public function testAssertConstructor()
    {
        $assertionObject = $this->getMock('ZfcRbac\Assertion\AssertionInterface');

        $assertionSet = new AssertionSet([
            $assertionObject
        ]);

        $this->assertEquals('AND', $assertionSet->getCondition());
        $this->assertEquals([$assertionObject], $assertionSet->getAssertions());
    }

    public function testSettersAndGetters()
    {
        $assertionSet = new AssertionSet();
        $assertionObject = $this->getMock('ZfcRbac\Assertion\AssertionInterface');

        $assertionSet->setCondition(AssertionSet::CONDITION_OR);
        $assertionSet->setAssertion($assertionObject, 'foo');

        $this->assertEquals('OR', $assertionSet->getCondition());
        $this->assertEquals(['foo' => $assertionObject], $assertionSet->getAssertions());
        $this->assertTrue($assertionSet->hasAssertion('foo'));
        $this->assertEquals($assertionObject, $assertionSet->getAssertion('foo'));

    }


    public function testThrowExceptionForInvalidAssertionName()
    {
        $assertionSet = new AssertionSet();
        $this->setExpectedException('ZfcRbac\Exception\InvalidArgumentException');
        $assertionSet->getAssertion('foo');
    }

    public function testIterator()
    {
        $assertionObject = $this->getMock('ZfcRbac\Assertion\AssertionInterface');

        $assertionSet = new AssertionSet([
            'foo' => $assertionObject,
            'bar' => $assertionObject
        ]);

        $assertinName = null;
        $assertion = null;
        foreach ($assertionSet as $key=>$value) {
            $assertinName = $key;
            $assertion = $value;
            break;
        }
        $this->assertEquals('foo', $assertinName);
        $this->assertEquals($assertionObject, $assertion);
    }

    public function testThrowExceptionForInvalidConditionInAssertionsSetter()
    {
        $assertionObject = $this->getMock('ZfcRbac\Assertion\AssertionInterface');
        $assertionSet = new AssertionSet();
        $assertionMap = [
            'assertions' => [
                'foo' => $assertionObject,
                'bar' => $assertionObject
            ],
            'condition' => 'WRONG'
        ];

        $this->setExpectedException('ZfcRbac\Exception\InvalidArgumentException');

        $assertionSet->setAssertions($assertionMap);
    }

    public function testThrowExceptionForInvalidConditionWhileAsserting()
    {
        $assertionObject = $this->getMock('ZfcRbac\Assertion\AssertionInterface');
        $authorizationService = $this->getMock('ZfcRbac\Service\AuthorizationService', [], [], '', false);
        $assertionMap = [
            'assertions' => [
                'foo' => $assertionObject,
                'bar' => $assertionObject
            ],
        ];
        $assertionSet = new AssertionSet($assertionMap);
        $assertionSet->setCondition('WRONG');

        $this->setExpectedException('ZfcRbac\Exception\InvalidArgumentException');

        $assertionSet->assert($authorizationService);

    }
    public function testConditionInSetter()
    {
        $assertionObject = $this->getMock('ZfcRbac\Assertion\AssertionInterface');
        $assertionSet = new AssertionSet();
        $assertionMap = [
            'assertions' => [
                'foo' => $assertionObject,
                'bar' => $assertionObject
            ],
            'condition' => AssertionSet::CONDITION_OR
        ];

        $assertionSet->setAssertions($assertionMap);
        $this->assertEquals(AssertionSet::CONDITION_OR, $assertionSet->getCondition());
    }

    public function testAssertWithConditionAnd()
    {
        $authorizationService = $this->getMock('ZfcRbac\Service\AuthorizationService', [], [], '', false);
        $assertionSet = new AssertionSet();
        // Using an assertion object
        $assertionTrue = new SimpleTrueAssertion();
        $assertionFalse = new SimpleFalseAssertion();
        $assertionSet->setAssertions([
            $assertionTrue,
            $assertionFalse
        ]);
        $this->assertFalse($assertionSet->assert($authorizationService, null));

        $assertionSet->setAssertions([
            $assertionTrue,
            $assertionTrue
        ]);
        $this->assertTrue($assertionSet->assert($authorizationService, null));
    }

    public function testAssertWithConditionOr()
    {
        $authorizationService = $this->getMock('ZfcRbac\Service\AuthorizationService', [], [], '', false);
        $assertionSet = new AssertionSet();
        $assertionSet->setCondition(AssertionSet::CONDITION_OR);
        // Using an assertion object
        $assertionTrue = new SimpleTrueAssertion();
        $assertionFalse = new SimpleFalseAssertion();
        $assertionSet->setAssertions([
            $assertionTrue,
            $assertionFalse
        ]);
        $this->assertTrue($assertionSet->assert($authorizationService, null));

        $assertionSet->setAssertions([
            $assertionTrue,
            $assertionTrue
        ]);
        $this->assertTrue($assertionSet->assert($authorizationService, null));

        $assertionSet->setAssertions([
            $assertionFalse,
            $assertionFalse
        ]);
        $this->assertFalse($assertionSet->assert($authorizationService, null));

    }
}
