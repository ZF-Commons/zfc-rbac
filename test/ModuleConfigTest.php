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

namespace ZfcRbacTest;

use PHPUnit\Framework\TestCase;
use ZfcRbac\ModuleConfig;

/**
 * @author  Bas Kamer <baskamer@gmail.com>
 * @covers  \ZfcRbac\ModuleConfig
 */
class ModuleConfigTest extends TestCase
{
    public function testCanBeInvoked(): void
    {
        $moduleConfig = new ModuleConfig();

        static::assertTrue(is_callable($moduleConfig));
    }

    public function testGetArrayWith(): void
    {
        $moduleConfig = new ModuleConfig();
        $config = $moduleConfig->__invoke();

        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('zfc_rbac', $config);
        $this->assertArrayHasKey('dependencies', $config);
    }
}
