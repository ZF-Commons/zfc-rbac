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
namespace ZfcRbacTest\Options;

use PHPUnit\Framework\TestCase;
use ZfcRbac\Options\ModuleOptions;

/**
 * @covers \ZfcRbac\Options\ModuleOptions
 */
class ModuleOptionsTest extends TestCase
{
    public function testAssertModuleDefaultOptions(): void
    {
        /** @var \ZfcRbac\Options\ModuleOptions $moduleOptions */
        $moduleOptions = new \ZfcRbac\Options\ModuleOptions();

        $this->assertEquals('guest', $moduleOptions->getGuestRole());
        $this->assertInternalType('array', $moduleOptions->getRoleProvider());
        $this->assertInternalType('array', $moduleOptions->getAssertionMap());
    }

    public function testSettersAndGetters(): void
    {
        $moduleOptions = new ModuleOptions([
            'guest_role'               => 'unknown',
            'role_provider'            => [],
            'assertion_map'            => [
                'foo' => 'bar',
            ],
        ]);

        $this->assertEquals('unknown', $moduleOptions->getGuestRole());
        $this->assertEquals([], $moduleOptions->getRoleProvider());
        $this->assertEquals(['foo' => 'bar'], $moduleOptions->getAssertionMap());
    }

    public function testThrowExceptionIfMoreThanOneRoleProviderIsSet(): void
    {
        $this->expectException('ZfcRbac\Exception\RuntimeException');
        $moduleOptions = new ModuleOptions();
        $moduleOptions->setRoleProvider(['foo', 'bar']);
    }
}
