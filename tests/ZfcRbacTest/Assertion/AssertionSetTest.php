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
    public function assertionMapProvider()
    {
        return [
            // simple
            [
                [
                    'assertion1',
                    'assertion2'
                ],
                [
                    'assertion1',
                    'assertion2'
                ]
            ],
            // named
            [
                [
                    'key1' => 'assertion1',
                    'key2' => 'assertion2'
                ],
                [
                    'key1' => 'assertion1',
                    'key2' => 'assertion2'
                ]
            ],
            // simple in assertion sub array
            [
                [
                    'assertions' => [
                        'assertion1',
                        'assertion2'
                    ]
                ],
                [
                    'assertion1',
                    'assertion2'
                ]
            ],
            // named in assertion sub array
            [
                [
                    'assertions' => [
                        'key1' => 'assertion1',
                        'key2' => 'assertion2'
                    ]
                ],
                [
                    'key1' => 'assertion1',
                    'key2' => 'assertion2'
                ]
            ],
        ];
    }

    /**
     * @dataProvider assertionMapProvider
     */
    public function testSetAssertions($assertionMap, $expected)
    {
        $assertionSet = new AssertionSet($assertionMap);
        $reflProperty = new \ReflectionProperty($assertionSet, 'assertions');
        $reflProperty->setAccessible(true);

        $this->assertEquals($expected, $reflProperty->getValue($assertionSet));
    }

    public function testDefaultCondition()
    {
        $assertionSet  = new AssertionSet();

        $reflProperty = new \ReflectionProperty($assertionSet, 'condition');
        $reflProperty->setAccessible(true);

        $this->assertEquals(AssertionSet::CONDITION_AND, $reflProperty->getValue($assertionSet));
    }

    public function testSetCondition()
    {
        $assertionMap = [
            'condition' => AssertionSet::CONDITION_OR
        ];

        $assertionSet = new AssertionSet($assertionMap);
        $reflProperty = new \ReflectionProperty($assertionSet, 'condition');
        $reflProperty->setAccessible(true);

        $this->assertEquals(AssertionSet::CONDITION_OR, $reflProperty->getValue($assertionSet));
    }

    public function testThrowExceptionForInvalidCondition()
    {
        $assertionObject = $this->getMock('ZfcRbac\Assertion\AssertionInterface');
        $assertionMap = [
            'assertions' => [
                'foo' => $assertionObject,
                'bar' => $assertionObject
            ],
            'condition' => 'WRONG'
        ];

        $this->setExpectedException('ZfcRbac\Exception\InvalidArgumentException');

        $assertionSet = new AssertionSet($assertionMap);
    }

    public function testAssertNoAssertions()
    {
        $authorizationService = $this->getMock('ZfcRbac\Service\AuthorizationService', [], [], '', false);
        $assertionSet = new AssertionSet();
        $this->assertTrue($assertionSet->assert($authorizationService, null));
    }

    public function testAssertWithConditionAND()
    {
        $authorizationService = $this->getMock('ZfcRbac\Service\AuthorizationService', [], [], '', false);
        $assertionTrue = new SimpleTrueAssertion();
        $assertionFalse = new SimpleFalseAssertion();
        $assertionMap = [
            'condition' => AssertionSet::CONDITION_AND,
            'assertions' => [
                $assertionTrue,
                $assertionFalse
            ]
        ];
        $assertionSet = new AssertionSet($assertionMap);
        $this->assertFalse($assertionSet->assert($authorizationService, null));

        $assertionMap = [
            'condition' => AssertionSet::CONDITION_AND,
            'assertions' => [
                $assertionTrue,
                $assertionTrue
            ]
        ];
        $assertionSet = new AssertionSet($assertionMap);
        $this->assertTrue($assertionSet->assert($authorizationService, null));
    }

    public function testAssertWithConditionOR()
    {
        $authorizationService = $this->getMock('ZfcRbac\Service\AuthorizationService', [], [], '', false);
        $assertionTrue = new SimpleTrueAssertion();
        $assertionFalse = new SimpleFalseAssertion();
        $assertionMap = [
            'condition' => AssertionSet::CONDITION_OR,
            'assertions' => [
                $assertionTrue,
                $assertionFalse
            ]
        ];
        $assertionSet = new AssertionSet($assertionMap);
        $this->assertTrue($assertionSet->assert($authorizationService, null));

        $assertionMap = [
            'condition' => AssertionSet::CONDITION_OR,
            'assertions' => [
                $assertionFalse,
                $assertionFalse
            ]
        ];
        $assertionSet = new AssertionSet($assertionMap);
        $this->assertFalse($assertionSet->assert($authorizationService, null));
    }
}
