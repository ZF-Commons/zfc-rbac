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

namespace ZfcRbacTest\Assertion;

use PHPUnit\Framework\TestCase;
use ZfcRbac\Assertion\AssertionContainerInterface;
use ZfcRbac\Assertion\AssertionInterface;
use ZfcRbac\Assertion\AssertionSet;
use ZfcRbac\Exception\InvalidArgumentException;
use ZfcRbac\Identity\IdentityInterface;
use ZfcRbacTest\Asset\SimpleAssertion;

/**
 * @covers \ZfcRbac\Assertion\AssertionSet
 */
class AssertionSetTest extends TestCase
{
    public function testImplementsAssertionInterface()
    {
        $assertionContainer = $this->getMockBuilder(AssertionContainerInterface::class)->getMock();
        $assertionSet = new AssertionSet($assertionContainer, []);

        $this->assertInstanceOf(AssertionInterface::class, $assertionSet);
    }

    public function testWhenNoAssertionsArePresentTheAssertionWillFail()
    {
        $assertionContainer = $this->getMockBuilder(AssertionContainerInterface::class)->getMock();
        $assertionSet = new AssertionSet($assertionContainer, []);

        $this->assertFalse($assertionSet->assert('foo'));
    }

    public function testAcceptsAnAndCondition()
    {
        $assertionContainer = $this->getMockBuilder(AssertionContainerInterface::class)->getMock();
        $assertionSet = new AssertionSet($assertionContainer, ['condition' => AssertionSet::CONDITION_AND]);

        $this->assertFalse($assertionSet->assert('foo'));
    }

    public function testAcceptsAnOrCondition()
    {
        $assertionContainer = $this->getMockBuilder(AssertionContainerInterface::class)->getMock();
        $assertionSet = new AssertionSet($assertionContainer, ['condition' => AssertionSet::CONDITION_OR]);

        $this->assertFalse($assertionSet->assert('foo'));
    }

    public function testThrowsExceptionForAnUnknownCondition()
    {
        $assertionContainer = $this->getMockBuilder(AssertionContainerInterface::class)->getMock();

        $this->expectException(InvalidArgumentException::class);
        new AssertionSet($assertionContainer, ['condition' => 'unknown']);
    }

    public function testWhenNoConditionIsGivenAndIsUsed()
    {
        $fooAssertion = new SimpleAssertion(true);
        $barAssertion = new SimpleAssertion(false);

        $assertionContainer = $this->getMockBuilder(AssertionContainerInterface::class)->getMock();
        $assertionSet = new AssertionSet($assertionContainer, ['fooFactory', 'barFactory']);

        $assertionContainer->expects($this->at(0))->method('get')->with('fooFactory')->willReturn($fooAssertion);
        $assertionContainer->expects($this->at(1))->method('get')->with('barFactory')->willReturn($barAssertion);

        $this->assertFalse($assertionSet->assert('permission'));

        $this->assertTrue($fooAssertion->gotCalled());
        $this->assertTrue($barAssertion->gotCalled());
    }

    public function testAndConditionWillBreakEarlyWithFailure()
    {
        $fooAssertion = new SimpleAssertion(false);
        $barAssertion = new SimpleAssertion(true);

        $assertionContainer = $this->getMockBuilder(AssertionContainerInterface::class)->getMock();
        $assertionSet = new AssertionSet($assertionContainer, ['fooFactory', 'barFactory', 'condition' => AssertionSet::CONDITION_AND]);

        $assertionContainer->expects($this->at(0))->method('get')->with('fooFactory')->willReturn($fooAssertion);

        $this->assertFalse($assertionSet->assert('permission'));

        $this->assertTrue($fooAssertion->gotCalled());
        $this->assertFalse($barAssertion->gotCalled());
    }

    public function testOrConditionWillBreakEarlyWithSuccess()
    {
        $fooAssertion = new SimpleAssertion(true);
        $barAssertion = new SimpleAssertion(false);

        $assertionContainer = $this->getMockBuilder(AssertionContainerInterface::class)->getMock();
        $assertionSet = new AssertionSet($assertionContainer, ['fooFactory', 'barFactory', 'condition' => AssertionSet::CONDITION_OR]);

        $assertionContainer->expects($this->at(0))->method('get')->with('fooFactory')->willReturn($fooAssertion);

        $this->assertTrue($assertionSet->assert('permission'));

        $this->assertTrue($fooAssertion->gotCalled());
        $this->assertFalse($barAssertion->gotCalled());
    }

    public function testAssertionsAsStringsAreCached()
    {
        $fooAssertion = new SimpleAssertion(true);

        $assertionContainer = $this->getMockBuilder(AssertionContainerInterface::class)->getMock();
        $assertionSet = new AssertionSet($assertionContainer, ['fooFactory']);

        $assertionContainer->expects($this->once())->method('get')->with('fooFactory')->willReturn($fooAssertion);

        $this->assertTrue($assertionSet->assert('permission'));
        $this->assertTrue($assertionSet->assert('permission'));

        $this->assertTrue($fooAssertion->gotCalled());
        $this->assertSame(2, $fooAssertion->calledTimes());
    }

    public function testUsesAssertionsAsStrings()
    {
        $fooAssertion = new SimpleAssertion(true);

        $assertionContainer = $this->getMockBuilder(AssertionContainerInterface::class)->getMock();
        $assertionSet = new AssertionSet($assertionContainer, ['fooFactory']);

        $assertionContainer->expects($this->once())->method('get')->with('fooFactory')->willReturn($fooAssertion);

        $this->assertTrue($assertionSet->assert('permission'));

        $this->assertTrue($fooAssertion->gotCalled());
    }

    public function testUsesAssertionsAsInstances()
    {
        $fooAssertion = new SimpleAssertion(true);

        $assertionContainer = $this->getMockBuilder(AssertionContainerInterface::class)->getMock();
        $assertionSet = new AssertionSet($assertionContainer, [$fooAssertion]);

        $this->assertTrue($assertionSet->assert('permission'));

        $this->assertTrue($fooAssertion->gotCalled());
    }

    public function testUsesAssertionsAsCallables()
    {
        $called = false;
        $fooAssertion = function ($permission, IdentityInterface $identity = null, $context = null) use (&$called) {
            $called = true;

            return true;
        };

        $assertionContainer = $this->getMockBuilder(AssertionContainerInterface::class)->getMock();
        $assertionSet = new AssertionSet($assertionContainer, [$fooAssertion]);

        $this->assertTrue($assertionSet->assert('permission'));

        $this->assertTrue($called);
    }

    public function testUsesAssertionsAsArrays()
    {
        $fooAssertion = new SimpleAssertion(true);
        $barAssertion = new SimpleAssertion(true);

        $assertionContainer = $this->getMockBuilder(AssertionContainerInterface::class)->getMock();
        $assertionSet = new AssertionSet($assertionContainer, ['fooFactory', ['barFactory']]);

        $assertionContainer->expects($this->at(0))->method('get')->with('fooFactory')->willReturn($fooAssertion);
        $assertionContainer->expects($this->at(1))->method('get')->with('barFactory')->willReturn($barAssertion);

        $this->assertTrue($assertionSet->assert('permission'));

        $this->assertTrue($fooAssertion->gotCalled());
        $this->assertTrue($barAssertion->gotCalled());
    }

    public function testThrowExceptionForInvalidAssertion()
    {
        $fooAssertion = new \stdClass();

        $assertionContainer = $this->getMockBuilder(AssertionContainerInterface::class)->getMock();
        $assertionSet = new AssertionSet($assertionContainer, [$fooAssertion]);

        $this->expectException(InvalidArgumentException::class);
        $this->assertTrue($assertionSet->assert('permission'));
    }

    /**
     * @dataProvider dpMatrix
     */
    public function testMatrix(array $assertions, bool $expectedResult, array $assertionCalledCount)
    {
        $assertionContainer = $this->getMockBuilder(AssertionContainerInterface::class)->getMock();
        $assertionSet = new AssertionSet($assertionContainer, $assertions);

        $this->assertSame($expectedResult, $assertionSet->assert('permission'));

        $this->assertionsCalled($assertions, $assertionCalledCount);
    }

    private function assertionsCalled(array $assertions, array $assertionCalledCount)
    {
        unset($assertions['condition']);
        foreach ($assertions as $key => $assertion) {
            if (is_array($assertion)) {
                $this->assertionsCalled($assertion, $assertionCalledCount[$key]);
            } else {
                /** @var SimpleAssertion $assertion */
                $this->assertSame($assertionCalledCount[$key], $assertion->calledTimes());
            }
        }
    }

    public function dpMatrix()
    {
        return [
            // no assertions will fail
            [[], false, []],

            // one failure, one success
            [['condition' => AssertionSet::CONDITION_AND, new SimpleAssertion(false)], false, [1]],
            [['condition' => AssertionSet::CONDITION_AND, new SimpleAssertion(true)], true, [1]],

            // one failure, one success
            [['condition' => AssertionSet::CONDITION_OR, new SimpleAssertion(false)], false, [1]],
            [['condition' => AssertionSet::CONDITION_OR, new SimpleAssertion(true)], true, [1]],

            // break early for AND condition with failure
            [['condition' => AssertionSet::CONDITION_AND, new SimpleAssertion(false), new SimpleAssertion(false)], false, [1, 0]],

            // break early for OR condition with success
            [['condition' => AssertionSet::CONDITION_OR, new SimpleAssertion(true), new SimpleAssertion(false)], true, [1, 0]],

            // nested assertions
            [['condition' => AssertionSet::CONDITION_OR, new SimpleAssertion(false), [new SimpleAssertion(true)]], true, [1, [1]]],
        ];
    }
}
