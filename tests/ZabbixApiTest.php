<?php

/*
 * This file is part of PhpZabbixApi.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright The MIT License (MIT)
 * @author confirm IT solutions GmbH, Rathausstrase 14, CH-6340 Baar
 */

namespace ZabbixApi\Tests;

use PHPUnit_Framework_TestCase as TestCase;
use ZabbixApi\ZabbixApi;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class ZabbixApiTest extends TestCase
{
    public function testZabbixApiClass()
    {
        $this->assertTrue(class_exists('ZabbixApi\ZabbixApi'));

        $rc = new \ReflectionClass('ZabbixApi\ZabbixApi');

        $this->assertGreaterThanOrEqual(405, $rc->getMethods(\ReflectionMethod::IS_PUBLIC));
    }

    public function testZabbixApiConnectionNotTriggered()
    {
        $zabbix = new ZabbixApi('http://localhost/json_rpc.php');
        $zabbix = new ZabbixApi('http://localhost/json_rpc.php', 'zabbix');
        $zabbix = new ZabbixApi('http://localhost/json_rpc.php', '', 'very_secret');

        $this->assertSame('http://localhost/json_rpc.php', $zabbix->getApiUrl());
    }

    /**
     * @expectedException \ZabbixApi\Exception
     * @expectedExceptionMessage Could not connect to "http://not.found.tld/json_rpc.php"
     */
    public function testZabbixApiConnectionError()
    {
        new ZabbixApi('http://not.found.tld/json_rpc.php', 'zabbix', 'very_secret_pass');
    }
}
